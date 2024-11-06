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
        $limit = $entity->getPostLimit();
        $boards = $entity->getIdBoards();

        return (in_array($boardId, $boards)) ||
            ($boards != false && $limit >= 1 && $this->utils->setting('enable_global_limit'));
    }

    public function buildErrorMessage(PostLimitEntity $entity): void
    {
        global $user_info;

        $postCount = $entity->getPostCount();
        $limit = $entity->getPostLimit();

        if ($postCount <= $limit) {
            return;
        }

        $replacements = [
            'username' => $user_info['name'],
            'nameColor' => $user_info['name_color'],
            'linkColor' => $user_info['link_color'],
            'limit' => $entity->getPostLimit(),
        ];

        $find = $replace = [];

        foreach ($replacements as $f => $r) {
            $find[] = '{' . $f . '}';
            $replace[] = $r;
        }

        $customMessage = $this->utils->setting('custom_message', false);

        fatal_lang_error(str_replace($find, $replace, $customMessage ??
            $this->utils->text('message_default')));
    }

    public function buildAlert(PostLimitEntity $entity): bool
    {
        global $user_info;

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

    public function updateCount(int $userId): void
    {
        $this->repository->updateCount($userId);
    }

    protected function checkSeeProfilePage(): void
    {
        global $context, $user_info;

        $message = '';

        if (!allowedTo(PostLimit::NAME . '_can_set_post_limit') || $user_info['is_guest']) {
            $message = $this->utils->text('message_cannot');
        } elseif ($context['member']['group_id'] == 1) {
            $message = $this->utils->text('message_cannot_admin');
        } elseif ($context['user']['is_owner']) {
            $message = $this->utils->text('message_cannot_own');
        }

        if (!empty($message)) {
            fatal_lang_error($message, true);
        }
    }

    protected function setTemplate(): void
    {
        global $context, $txt, $user_info, $scripturl;

        loadtemplate(PostLimit::NAME);

        $context['sub_template'] = 'postLimit_profile_page';
        $context += [
            'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
        ];
        $context['user']['is_owner'] = (int) $context['member']['id'] === (int) $user_info['id'];
        $context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
    }

    public function profilePage(PostLimitEntity $entity): void
    {
        global $context;

        $this->checkSeeProfilePage();
        $this->setTemplate();

        $context[PostLimit::NAME] = $entity;

        if ($this->utils->isRequestSet('save')) {
            checkSession();

            if (!$this->utils->request(PostLimitEntity::POST_LIMIT)) {
                return;
            }

            $entity->setIdBoards(array_filter(explode(',',
                preg_replace('/[^0-9,]/',
                    '', (string) $this->utils->request(PostLimitEntity::ID_BOARDS))
            )));

            $entity->setPostLimit($this->utils->request(PostLimitEntity::POST_LIMIT));

            $this->repository->update($entity);

            redirectexit('action=profile;area='. strtolower(PostLimit::NAME) .';u='. $context['member']['id']);
        }
    }
}
