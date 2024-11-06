<?php

namespace PostLimit;

class PostLimitRepository
{
    /**
     * @var mixed
     */
    private $db;

    public function __construct()
    {
        $this->db = $GLOBALS['smcFunc'];
    }
    public function insert(PostLimitEntity $entity): ?PostLimitEntity
    {
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
            '',
            'DELETE
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

    public function getInsertedId(): int
    {
        return $this->db['db_insert_id']('{db_prefix}' . PostLimitEntity::TABLE, PostLimitEntity::ID_USER);
    }

    public function insertBackgroundTask(array $content): void
    {
        $this->db['db_insert']('insert',
            '{db_prefix}background_tasks',
            ['task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'],
            ['$sourcedir/tasks/PostLimitNotify.php', 'PostLimitNotify', $this->db['json_encode']($content), 0],
            ['id_task']
        );
    }

    public function insertAlert(array $backgroundTaskDetails): void
    {
        $this->db['db_insert']('insert',
            '{db_prefix}user_alerts',
            ['alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string',
                'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'],
            [$backgroundTaskDetails['time'], $backgroundTaskDetails['idUser'], $backgroundTaskDetails['idUser'], '',
                strtolower(PostLimit::NAME), $backgroundTaskDetails['idUser'], '', 0, ''],
            ['id_alert']
        );

        updateMemberData($backgroundTaskDetails['idUser'], array('alerts' => '+'));
    }

    public function deleteAlerts(int $userId): void
    {
        $this->db['db_query']('', '
		DELETE FROM {db_prefix}user_alerts
		WHERE id_member = {int:id_member}
            AND is_read = 0
            AND content_type = {string:content_type}
            AND content_id = {int:content_id}',
        [
            'id_member' => $userId,
            'content_type' => strtolower(PostLimit::NAME),
            'content_id' => $userId,
        ]
        );
    }


    protected function fetchAssoc($result): ?array
    {
        return $this->db['db_fetch_assoc']($result);
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