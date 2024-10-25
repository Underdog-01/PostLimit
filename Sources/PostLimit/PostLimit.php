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

class PostLimit
{
    public const NAME = 'PostLimit';
    private PostLimitService $service;

    public function __construct(?PostLimitService $service = null)
    {
        //No DI :(
        $this->service = $service ?? new PostLimitService();
    }

    public function autoload(&$classMap): void
    {
        $classMap[self::NAME . '\\'] = self::NAME . '/';
    }

    public function handle(): void
    {
        if (!$this->service->isEnable()){
            return;
        }


        /* PostLimit mod */
        if (PostLimit::tools()->enable('enable') && !$user_info['is_guest'])
        {
            $pl_postLimit = new PostLimit($user_info['id'], $board_info['id']);
            $context['postLimit'] = array(
                'message' => '',
                'title' => ''
            );

            /* Is this board limited? or is this user under a global limit? */
            if ($pl_postLimit->isBoardLimited() || ($pl_postLimit->getBoards() == false && $pl_postLimit->getLimit() >= 1 && PostLimit::tools()->enable('enable_global_limit')))
            {
                /* Get the user's post limit */
                $pl_userLimit = $pl_postLimit->getLimit();

                /* Get the user's current post count */
                $pl_userCount = $pl_postLimit->getCount();

                $context['postLimit']['title'] = sprintf(PostLimit::tools()->getText('message_title'), $user_info['name']);

                /* Define what we are gonna do */
                if ($pl_userCount < $pl_userLimit)
                {
                    /* Just how many messages are left? */
                    $pl_messagesLeft = $pl_userLimit - $pl_userCount;

                    if ($pl_messagesLeft <= 3)
                        $context['postLimit']['message'] = sprintf(PostLimit::tools()->getText('message'), $pl_messagesLeft);
                }

                elseif ($pl_userCount >= $pl_userLimit)
                    fatal_error($pl_postLimit->customMessage($user_info['name']), false);
            }
        }
        /* PostLimit mod */

    }
}
