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

class PostLimitAdmin
{
    public const ACTIONS = [
        'settings',
    ];
    public const URL = 'action=admin;area=postlimit';
    protected PostLimitService $service;
    protected PostLimitUtils $utils;

    public function __construct(PostLimitService $service, PostLimitUtils $utils)
    {
        $this->utils = $utils;
        $this->service = $service;
    }

    public function menu(&$admin_areas): void
    {
        $this->loadRequiredFiles();

        $admin_areas['config']['areas'][strtolower(PostLimit::NAME)] = array(
            'label' => $this->utils->text('admin_panel'),
            'function' => [$this, 'main'],
            'icon' => 'posts.gif',
            'subsections' => [
                'general' => [$this->utils->text('admin_panel_settings')],
            ],
        );
    }

    public function main(): void
    {
        global $context;

        $context[$context['admin_menu_name']]['tab_data'] = [
            'title' => $this->utils->text('admin_panel'),
            'description' => $this->utils->text('admin_panel_desc'),
            'tabs' => [
                self::ACTIONS[0] => []
            ],
        ];

        $action = $this->utils->request('sa');
        $action = $action && in_array($action, self::ACTIONS, true) ?
        $action : self::ACTIONS[0];

        $this->setContext($action);
        $this->{$action}();
    }

    public function settings(): void
    {
        $config_vars = [
            ['check', PostLimit::NAME . '_enable','subtext' => $this->utils->text('enable_sub')],
            ['large_text', PostLimit::NAME . '_custom_message', 'subtext' => $this->utils->text('custom_message_sub')],
            ['check', PostLimit::NAME . '_enable_global_limit','subtext' => $this->utils->text('enable_global_limit_sub')],
        ];

        if ($this->utils->request('save'))
        {
            checkSession();
            saveDBSettings($config_vars);
            redirectexit(self::URL);
        }

        prepareDBSettingContext($config_vars);
    }

    public function permissions(&$permissionGroups, &$permissionList)
    {
        $simple = PostLimit::NAME . '_per_simple';
        $classic = PostLimit::NAME . '_per_classic';

        $permissionList['membergroup'][PostLimit::NAME . '_can_set_post_limit'] = [
            false,
            $classic,
            $simple
        ];
        $permissionGroups['membergroup']['simple'] = [$simple];
        $permissionGroups['membergroup']['classic'] = [$classic];
    }

    protected function setContext(string $action): void
    {
        global $context, $scripturl, $txt;

        $context['sub_action'] = $action;
        $context['page_title'] = $this->utils->text('admin_' . $action);
        $context['post_url'] = $scripturl . '?' . self::URL .';save';
        $context['settings_title'] = $context['page_title'];
    }

    protected function loadRequiredFiles(): void
    {
        global $sourcedir;

        isAllowedTo('admin_forum');

        loadLanguage(PostLimit::NAME);
        loadtemplate(PostLimit::NAME);

        require_once($sourcedir . '/ManageSettings.php');
        require_once($sourcedir . '/ManageServer.php');
    }
}