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

// No DI :(
require 'PostLimitService.php';
require 'PostLimitAdmin.php';
require 'PostLimitUtils.php';
require 'PostLimitEntity.php';
require 'PostLimitRepository.php';

class PostLimit
{
    public const NAME = 'PostLimit';
    public const DEFAULT_PERCENTAGE_TO_ALERT = 80;
    public const DEFAULT_POST_LIMIT = 0;
    private PostLimitService $service;

    public function __construct()
    {
        //No DI :(
        $this->service = new PostLimitService();
    }

    // scheduled_tasks task column has a 24 char limit :(
    public static function s(): bool
    {
        (new PostLimitRepository())->resetPostCount();

        return true;
    }

    public function handle($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
    {
        $posterId = (int) $posterOptions['id'];
        $boardId = (int) $topicOptions['board'];

        if (!$this->service->isEnable() || $posterId === 0) {
            return;
        }

        $this->updateCount($posterId);

        $entity = $this->service->getEntityByUser($posterId);

        if (!$this->service->isUserLimited($entity, $boardId)) {
            return;
        }

        if ($this->service->buildAlert($entity)) {
            return;
        }

        $this->service->buildErrorMessage($entity);
    }

    public function updateCount(int $posterId): void
    {
        $this->service->updateCount($posterId);
    }

    public function createCount(&$regOptions, &$theme_vars, &$memberID)
    {
        $this->service->createDefaultEntity($memberID);
    }

    public function profile(&$profileAreas): void
    {
        global $txt, $user_info;

        if (!$this->service->isEnable() || $user_info['is_guest']) {
            return;
        }

        loadLanguage(PostLimit::NAME);

        $entity = $this->service->getEntityByUser((int) $user_info['id']);

        $profileAreas['info']['areas'][strtolower(self::NAME)] = [
            'label' => $txt[self::NAME . '_profile_panel'],
            'icon' => 'members',
            'function' => fn () => $this->service->profilePage($entity),
            'permission' => [
                'own' => 'is_not_guest',
                'any' => 'profile_view',
            ],
        ];
    }

    public function handleAlerts(array $content): void
    {

    }
}
