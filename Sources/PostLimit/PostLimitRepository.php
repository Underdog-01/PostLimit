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
            $set .= $name . ' = {' . $type . ':'. $name .'}, ';
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
                'columns' => array_keys(PostLimitEntity::COLUMNS),
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

        while ($row = $this->fetchAssoc($request)) {
            $entity = new PostLimitEntity($row);
        }

        $this->freeResult($request);

        return $entity;
    }
}