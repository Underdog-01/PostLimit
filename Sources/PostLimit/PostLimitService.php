<?php

declare(strict_types=1);

/**
 * Post Limit mod (SMF)
 *
 * @package PostLimit
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2024  Michel Mendiola
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace PostLimit;

class PostLimitService
{
    private PostLimitUtils $utils;
    private PostLimitRepository $repository;

    public function __construct(?PostLimitUtils $utils = null, ?PostLimitRepository $repository = null)
    {
        //No DI :(
        $this->utils = $utils ?? new PostLimitUtils();
        $this->repository = $repository ?? new PostLimitRepository();
    }

    public function getEntityByUser(int $userId): ?PostLimitEntity
    {
        $entity = $this->repository->getByUser($userId);
        return (!$entity && $userId !== 0) ? $this->createDefaultEntity($userId) : $entity;
    }

    public function createDefaultEntity(int $userId): ?PostLimitEntity
    {
        return $this->repository->insert(new PostLimitEntity([
            PostLimitEntity::ID_USER => $userId,
            PostLimitEntity::ID_BOARDS => [],
            PostLimitEntity::POST_LIMIT => PostLimit::DEFAULT_POST_LIMIT,
            PostLimitEntity::POST_COUNT => 0,
        ]));
    }

    public function isEnable(): bool
    {
        global $user_info;

        return !$user_info['is_guest'] || !$this->utils->setting('enable');
    }

    public function isUserLimited(PostLimitEntity $entity, int $boardId): bool
    {
        if ($entity->isUserExempted()) {
            return false;
        }

        $limit = $entity->getPostLimit();
        $boards = $entity->getIdBoards();

        return (in_array($boardId, $boards)) ||
            ($boards != false && $limit >= 1 && $this->utils->setting('enable_global_limit'));
    }

    public function buildErrorMessage(PostLimitEntity $entity, array $posterOptions): string
    {
        $replacements = [
            'username' => $posterOptions['name'],
            'limit' => $entity->getPostLimit(),
        ];

        $find = $replace = [];

        foreach ($replacements as $f => $r) {
            $find[] = '{' . $f . '}';
            $replace[] = $r;
        }

        $customMessage = $this->utils->setting('custom_message', false);

        return (str_replace($find, $replace, $customMessage ??
            $this->utils->text('message_default')));
    }

    public function buildAlert(PostLimitEntity $entity): bool
    {
        if ($entity->isUserExempted()) {
            return false;
        }

        $postCount = $entity->getPostCount();
        $limit = $entity->getPostLimit();

        $percentage = $this->utils->calculatePercentage($postCount, $limit);
        $postCountAlert = $this->utils->setting('post_count_alert');

        if ($percentage >= $postCountAlert) {
            $this->repository->insertBackgroundTask([
                'idUser' => $entity->getIdUser(),
                'time' => time(),
            ]);

            return true;
        }

       return false;
    }

    public function updateEntity(PostLimitEntity $entity): void
    {
        $this->repository->update($entity);
    }

    public function updateCount(int $userId): void
    {
        $this->repository->updateCount($userId);
    }
}
