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
    public const DEFAULT_POST_LEFT_TO_SHOW_NOTIFICATION = 3;
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
        if (!$this->service->isEnable() || !$this->service->isUserLimited()) {
            return;
        }

        $entity = $this->service->getEntityByUser();
        $postCount = $entity->getPostCount();
        $limit = $entity->getPostLimit();
        $messagesLeftCount = $limit - $postCount;

        if ($postCount < $limit && $messagesLeftCount <= self::DEFAULT_POST_LEFT_TO_SHOW_NOTIFICATION) {
            // @TODO: handle showing the notification on posting
            $notification = $this->service->getNotificationContent($messagesLeftCount);

            return;
        }

        if ($postCount >= $limit) {
            fatal_error($this->service->getFatalErrorMessage(), false);
        }
    }

    public function updateCount($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
    {
        $this->service->updateCount();
    }

    public function createCount(&$regOptions, &$theme_vars, &$memberID)
    {
        $this->service->createDefaultEntity($memberID);
    }

    public function profile(&$profileAreas): void
    {
        global $txt;

        if (!$this->service->isEnable()) {
            return;
        }

        $profileAreas['info']['areas'][strtolower(self::NAME)] = [
            'label' => $txt[self::NAME . 'profile_panel'],
            'icon' => 'members',
            'function' => fn () => $this->service->profilePage(),
            'permission' => [
                'own' => 'is_not_guest',
                'any' => 'profile_view',
            ],
        ];
    }
}
