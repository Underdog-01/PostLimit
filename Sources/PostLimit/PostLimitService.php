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

    public function getEntityByUser(?int $userId = null): ?PostLimitEntity
    {
        $entity = $this->repository->getByUser($userId ?? $this->userId);
        return $entity ?? $this->createDefaultEntity();
    }

    public function createDefaultEntity(?int $userId = null): ?PostLimitEntity
    {
        return $this->repository->insert(new PostLimitEntity([
            PostLimitEntity::ID_USER => $userId ?? $this->userId,
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

    public function updateCount(int $userId = 0): void
    {
        $this->repository->updateCount($userId ?? $this->userId);
    }

    protected function checkSeeProfilePage(): void
    {
        global $context;

        $message = '';

        if (!allowedTo(PostLimit::NAME . '_can_set_post_limit')) {
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
        $context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
        $context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
    }

    public function profilePage(): void
    {
        global $context;

        $this->checkSeeProfilePage();
        $this->setTemplate();

        $postLimit = $this->getEntityByUser((int) $context['member']['id']);

        $context[PostLimit::NAME] = $postLimit->toArray();

        if ($this->utils->request('save')) {
            checkSession();

            if (!$this->utils->request(PostLimitEntity::POST_LIMIT)) {
                return;
            }

            $postLimit->setIdBoards(explode(',',
                preg_replace('/[^0-9,]/',
                    '', $this->utils->request(PostLimitEntity::ID_BOARDS))
            ));

            $postLimit->setPostLimit($this->utils->request(PostLimitEntity::POST_LIMIT));
            $postLimit->setPostCount(0);
            $postLimit->setIdUser($context['member']['id']);

            $this->repository->update($postLimit);

            redirectexit('action=profile;area='. strtolower(PostLimit::NAME) .';u='. $context['member']['id']);
        }
    }
}
