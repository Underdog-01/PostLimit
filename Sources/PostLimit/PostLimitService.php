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
    private PostLimitEntity $entity;
    private int $boardId;
    private int $userId;

    public function __construct(?PostLimitUtils $utils = null, ?PostLimitRepository $repository = null)
    {
        global $user_info, $board;
        //No DI :(
        $this->utils = $utils ?? new PostLimitUtils();
        $this->repository = $repository ?? new PostLimitRepository();
        $this->boardId = $board;
        $this->userId = (int) $user_info['user_id'];
        $this->entity = $this->getEntityByUser();
    }

    public function getEntityByUser(): ?PostLimitEntity
    {
        $entity = $this->repository->getByUser($this->userId);
        return $entity ?? $this->createDefaultEntity();
    }

    public function createDefaultEntity(): ?PostLimitEntity
    {
        return $this->repository->insert(new PostLimitEntity([
            PostLimitEntity::ID_USER => $this->userId,
            PostLimitEntity::ID_BOARDS => [],
            PostLimitEntity::POST_LIMIT => $this->utils->setting('default_post_limit'),
            PostLimitEntity::POST_COUNT => 0,
        ]));
    }

    public function isEnable(): bool
    {
        global $user_info;

        return !$user_info['is_guest'] || !$this->utils->setting('enable');
    }

    public function isUserLimited(): bool
    {
        $limit = $this->entity->getPostLimit();
        $boards = $this->entity->getIdBoards();

        return $this->isBoardLimited() ||
            ($boards != false && $limit >= 1 && $this->utils->setting('enable_global_limit'));
    }

    public function isBoardLimited(): bool
    {
        if (empty($this->boardId)) {
            return false;
        }

        return in_array($this->boardId, $this->entity->getIdBoards());
    }

    public function getNotificationContent(int $messagesLeftCount = 0): array
    {
        global $user_info;

       return [
           'title' => sprintf($this->utils->text('message_title'), $user_info['name']),
           'message' => sprintf($this->utils->text('message'), $messagesLeftCount)
       ];
    }

    public function getFatalErrorMessage(): string
    {
        global $user_info;

        $replacements = [
            'username' => $user_info['name'],
            'nameColor' => $user_info['name_color'],
            'linkColor' => $user_info['link_color'],
            'limit' => $this->entity->getPostLimit(),
        ];

        $find = $replace = [];

        foreach ($replacements as $f => $r) {
            $find[] = '{' . $f . '}';
            $replace[] = $r;
        }

        $customMessage = $this->utils->setting('custom_message');

        return str_replace($find, $replace, $customMessage ?? $this->utils->text('message_default'));
    }

    public function updateCount(): void
    {
        $this->repository->updateCount($this->userId);
    }
}
