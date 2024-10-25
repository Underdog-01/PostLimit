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

    public function __construct(?PostLimitUtils $utils = null)
    {
        //No DI :(
        $this->utils = $utils ?? new PostLimitUtils();
    }

    public function isEnable(): bool
    {
        global $user_info;

        return !$user_info['is_guest'] || !$this->utils->setting('enable');
    }

    public function isBoardLimited(int $boardId = 0): bool
    {
        if (empty($boardId)) {
            return false;
        }

        $boards = $this->getBoards();

        return in_array($boardId, $boards);
    }

    public function getBoards(): array
    {
        global $sourcedir, $boards;

        require_once($sourcedir . '/Subs-Boards.php');

        getBoardTree();

        return $boards;
    }
}
