<?php

namespace PostLimit;

class PostLimitProfile
{
    protected PostLimitService $service;
    private PostLimitUtils $utils;

    public function __construct()
    {
        //No DI :(
        $this->service = new PostLimitService();
        $this->utils = new PostLimitUtils();
    }

    public function setArea(&$profileAreas): void
    {
        global $txt, $context;

        if (!$this->service->isEnable()) {
            return;
        }

        $this->setTemplate();

        $entity = $this->service->getEntityByUser((int) $context['id_member']);

        $profileAreas['info']['areas'][strtolower(PostLimit::NAME)] = [
            'label' => $txt[PostLimit::NAME . '_profile_panel'],
            'icon' => 'members',
            'function' => fn () => $this->displayPage($entity),
            'permission' => [
                'own' => 'is_not_guest',
                'any' => 'profile_view',
            ],
        ];
    }

    public function displayPage(PostLimitEntity $entity): void
    {
        global $context;

        $this->isAllowedTo();

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

            $this->service->updateEntity($entity);

            redirectexit('action=profile;area='. strtolower(PostLimit::NAME) .';u='. $context['member']['id']);
        }
    }

    protected function isAllowedTo(): void
    {
        global $context, $user_info;

        $message = '';

        if (!allowedTo(PostLimit::NAME . '_can_set_post_limit') || $user_info['is_guest']) {
            $message = $this->utils->text('message_cannot');
        } elseif ((int) $context['member']['group_id'] === 1) {
            $message = $this->utils->text('message_cannot_admin');
        } elseif ($context['user']['is_owner']) {
            $message = $this->utils->text('message_cannot_own');
        }

        if (!empty($message)) {
            fatal_lang_error(PostLimit::NAME . '_message_cannot_general', false, [$message]);
        }
    }

    protected function setTemplate(): void
    {
        global $context, $txt, $user_info, $scripturl;

        loadtemplate(PostLimit::NAME);
        loadLanguage(PostLimit::NAME);

        $context['sub_template'] = 'postLimit_profile_page';
        $context += [
            'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
        ];
        $context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['id_member'];
    }
}