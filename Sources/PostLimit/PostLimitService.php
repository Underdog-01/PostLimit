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
    }

    public function getEntityByUser(int $userId): PostLimitEntity
    {
        return $this->repository->getByUser($userId);
    }

    public function isEnable(): bool
    {
        global $user_info;

        return !$user_info['is_guest'] || !$this->utils->setting('enable');
    }

    public function isUserLimited(int $userId): bool
    {
        return !$this->isBoardLimited($board) || ($this->utils->setting('enable_global_limit'));
    }

    public function isBoardLimited(int $boardId = 0): bool
    {
        if (empty($boardId)) {
            return false;
        }

        return in_array($boardId, $this->getBoards());
    }

    public function getBoards(): array
    {
        global $sourcedir, $boards;

        require_once($sourcedir . '/Subs-Boards.php');

        getBoardTree();

        return $boards;
    }
}
