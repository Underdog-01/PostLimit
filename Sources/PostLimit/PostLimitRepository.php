<?php

namespace PostLimit;

class PostLimitRepository
{
	/**
	 * @var mixed
	 */
	private $db;
	protected const SETTINGS_TABLE_NAME = 'settings';
	protected const ALERTS_TABLE_NAME = 'user_alerts';

	protected const HOOK_NAME = 'integrate_create_post';

	public function __construct()
	{
		$this->db = $GLOBALS['smcFunc'];
	}
	public function insert(PostLimitEntity $entity): ?PostLimitEntity
	{
		$this->db['db_query'](
			'','
			DELETE
			FROM {db_prefix}' . PostLimitEntity::TABLE . '
			WHERE id_user = {int:userid}',
			['userid' => $entity->toArray()['idUser']]
		);

		$this->db['db_insert'](
			'insert',
			'{db_prefix}' . PostLimitEntity::TABLE,
			PostLimitEntity::COLUMNS,
			$entity->toArray(),
			(array) PostLimitEntity::ID_USER
		);

		return $this->getByUser($entity->getIdUser());
	}

	public function update(PostLimitEntity $entity): void
	{
		$this->db['db_query'](
			'',
			'UPDATE {db_prefix}' . PostLimitEntity::TABLE . '
			'. $this->buildSetUpdate($entity) .'
			WHERE {raw:columnName} = {int:userId}',
			$this->buildParams([
				'columnName' => PostLimitEntity::ID_USER,
				'userId' => $entity->getIdUser()
			], $entity)
		);
	}

	protected function buildParams(array $params, PostLimitEntity $entity): array
	{
		$entityAsArray = $entity->toArray();

		foreach (PostLimitEntity::COLUMNS as $name => $type) {
			$params[$name] = $entityAsArray[$entity->snakeToCamel($name)];
		}

		return $params;
	}

	protected function buildSetUpdate(PostLimitEntity $entity): string
	{
		$set = 'SET ';
		foreach (PostLimitEntity::COLUMNS as $name => $type) {
			$set .= ' ' . $name . ' = {' . $type . ':'. $name .'},';
		}

		return rtrim($set, ',');
	}

	public function getByUser(int $userId): ?PostLimitEntity
	{
		if (empty($userId)) {
			return null;
		}

		return $this->prepareData($this->db['db_query'](
			'',
			'SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} = {int:userId}',
			[
				'from' => PostLimitEntity::TABLE,
				'columns' => implode(',', array_keys(PostLimitEntity::COLUMNS)),
				'columnName' => PostLimitEntity::ID_USER,
				'userId' => $userId
			]
		));
	}

	public function deleteBy(array $ids, string $byKey = PostLimitEntity::ID_USER): bool
	{
		if (empty($ids)) {
			return false;
		}

		return $this->db['db_query'](
			'','
			DELETE
			FROM {db_prefix}' . PostLimitEntity::TABLE . '
		    WHERE ' . $byKey . ' IN({array_int:ids})',
			['ids' => $ids]
		);
	}

	public function updateCount($userId): void
	{
		$this->db['db_query'](
			'',
			'UPDATE {db_prefix}' . PostLimitEntity::TABLE . '
			SET post_count = post_count + 1
			WHERE {raw:columnName} = {int:userId}',
			[
				'columnName' => PostLimitEntity::ID_USER,
				'userId' => $userId
			]
		);
	}

	public function resetPostCount(): void
	{
		$this->db['db_query'](
			'',
			'UPDATE {db_prefix}' . PostLimitEntity::TABLE . '
			SET post_count = 0',
			[]
		);
	}

	public function getInsertedId(): int
	{
		return $this->db['db_insert_id']('{db_prefix}' . PostLimitEntity::TABLE, PostLimitEntity::ID_USER);
	}

	public function insertBackgroundTask(array $content): void
	{
		$this->db['db_insert']('insert',
			'{db_prefix}background_tasks',
			['task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'],
			['$sourcedir/PostLimit/PostLimitNotify.php', '\PostLimit\PostLimitNotify', $this->db['json_encode']($content), 0],
			['id_task']
		);
	}

	public function insertAlert(array $backgroundTaskDetails): void
	{
		$this->db['db_insert']('insert',
			'{db_prefix}' . self::ALERTS_TABLE_NAME,
			['alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string',
				'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'],
			[$backgroundTaskDetails['time'], $backgroundTaskDetails['idUser'], $backgroundTaskDetails['idUser'], '',
				strtolower(PostLimit::NAME), $backgroundTaskDetails['idUser'], '', 0, ''],
			['id_alert']
		);

		updateMemberData($backgroundTaskDetails['idUser'], array('alerts' => '+'));
	}

	public function getCreatePostHooks(): string
	{
		$hooks = '';
		$request = $this->db['db_query'](
			'',
			'SELECT {raw:value}
			FROM {db_prefix}{raw:from}
			WHERE {raw:columnName} = {string:hookName}',
			[
				'value' => 'value',
				'from' => self::SETTINGS_TABLE_NAME,
				'columnName' => 'variable',
				'hookName' => self::HOOK_NAME
			]
		);

		$hooks = $this->fetchRow($request);

		$this->freeResult($request);

		return $hooks[0];
	}

	public function updateCreatePostHooks(string $hooks): void
	{
		$this->db['db_query'](
			'',
			'UPDATE {db_prefix}{raw:from}
			SET {raw:value} = {string:updatedValue}
			WHERE {raw:columnName} = {string:hookName}',
			[
				'from' => self::SETTINGS_TABLE_NAME,
				'value' => 'value',
				'updatedValue' => $hooks,
				'columnName' => 'variable',
				'hookName' => self::HOOK_NAME
			]
		);
	}

	public function deleteAllAlerts()
	{
		global $sourcedir;

		// Use SMF built-in functions
		require_once($sourcedir . '/Profile-Modify.php');

		$users = [];
		$request = $this->db['db_query']('', '
		SELECT id_member, id_alert FROM {db_prefix}{raw:tableName}
		WHERE content_type = {string:identifier}',
			[
				'tableName' => self::ALERTS_TABLE_NAME,
				'identifier' => strtolower(PostLimit::NAME)
			]
		);

		while ($row = $this->fetchAssoc($request)) {
			if (!isset($users[$row['id_member']])) {
				$users[$row['id_member']] = [];
			}

			$users[$row['id_member']][] = $row['id_alert'];
		}

		foreach ($users as $userId => $alertsToDelete) {
			alert_delete($alertsToDelete, $userId);
		}
	}

	public function hasUnreadAlerts(int $userId): bool
	{
		$request = $this->db['db_query'](
			'',
			'SELECT content_type
			FROM {db_prefix}{raw:tableName}
			WHERE content_type = {string:contentType}
			AND id_member = {int:userId}
			AND is_read = 0 ',
			[
				'contentType' => strtolower(PostLimit::NAME),
				'tableName' => self::ALERTS_TABLE_NAME,
				'columnName' => 'content_type',
				'userId' => $userId
			]
		);

		return $this->db['db_num_rows']($request) !== 0;
	}

	protected function fetchAssoc($result): ?array
	{
		return $this->db['db_fetch_assoc']($result);
	}
	protected function fetchRow($result): ?array
	{
		return $this->db['db_fetch_row']($result);
	}
	protected function freeResult($result): void
	{
		$this->db['db_free_result']($result);
	}

	protected function prepareData(object $request): ?PostLimitEntity
	{
		$entity = null;

		// This only works for a single entity but thats OK
		while ($row = $this->fetchAssoc($request)) {
			$entity = new PostLimitEntity(array_map(function ($column) {
				return ctype_digit($column) ? ((int) $column) : explode(',', $column);
			}, $row));
		}
		$this->freeResult($request);

		return $entity;
	}
}