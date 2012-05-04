<?php

/**
 * Post Limit mod (SMF)
 *
 * @package SMF
 * @author Suki <missallsunday[at]simplemachines.org>
 * @copyright 2012 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.0
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/* Wrapper functions */
function wrapper_admin_dispatch(){ PostLimit::settingsDispatch(); }
function wrapper_admin_settings() { PostLimit::settings(); }
function wrapper_profile_page() { PostLimit::profilePage(); }

class PostLimit
{
	private $_user;
	private $_board;
	public static $tools;
	private $_params = array();
	private $_data = array();
	private $_rows = array();
	static private $_dbTableName = 'post_limit';

	public function __construct($user, $board = false)
	{
		if (empty($user))
			return false;

		if ($board)
			$this->_board = $board;

				$this->_user = $user;
		$this->_db = $this->db(self::$_dbTableName);
		$this->_params = array(
			'where' => 'id_user = {int:id_user}'
		);
		$this->_data = array(
			'id_user' => $this->_user
		);
		$this->_rows = array(
			'id_user' => 'id_user',
			'post_count' => 'post_count',
			'post_limit' => 'post_limit',
			'id_boards' => 'id_boards'
		);
	}

	public function updateCount()
	{
		/* Update! */
		$this->_params['set'] = 'post_count = post_count + 1';
		$this->_db->params($this->_params, $this->_data);
		$this->_db->updateData();
	}

	protected function getValue($row)
	{
		if (empty($row))
			return false;

		if (!in_array($this->_rows, $row))
			return false;

		$this->_params['rows'] = $row;
		$this->_db->params($this->_params, $this->_data);

		if ($this->_db->getData(null, true))
			return $this->_db->getData(null, true);

		else
			return false;
	}

	public function rowExists()
	{
		$return = $this->getValue($this->_rows['id_user']);

		if (!empty($return))
			return true;

		else
			return false;
	}

	public function getCount()
	{
		return $this->getValue($this->_rows['post_count']);
	}

	public function getLimit()
	{
		return $this->getValue($this->_rows['post_limit']);
	}

	public function getBoards()
	{
		$result = $this->getValue($this->_rows['id_boards']);

		if ($result)
			return explode(',', $this->_db->getData(null, true));

		else
			return false;
	}

	public function isBoardLimited()
	{
		if ($this->getBoards() != false)
		{
			if (in_array($this->_board, $this->getBoards()))
				return true;

			else
				return false;
		}

		else
			return false;
	}

	public function updateRow($data)
	{
		/* Update! */
		$this->_params['set'] = 'post_limit = {int:limit}, id_boards = {string:boards}';
		$this->_data['limit'] = $data['limit'];
		$this->_data['boards'] = $data['boards'];

		$this->_db->params($this->_params, $this->_data);
		$this->_db->updateData();
	}
	
	public function createRow($data)
	{
		$tdata = array(
			'id_user' => 'int',
			'id_boards' => 'string',
			'post_limit' => 'int',
			'post_count' => 'int'
		);
		$tvalues = array(
			$data['user'],
			$data['boards'],
			$data['limit'],
			0,
		);
		$indexes = array(
			'id_user'
		);

		/* Insert! */
		$this->_db->insertData($tdata, $tvalues, $indexes);
	}
	
	public function customMessage($name)
	{
		/* Add in the default replacements. */
		$replacements = array(
			'username' => $name,
			'limit' => $this->getLimit(),
		);

		/* Split the replacements up into two arrays, for use with str_replace */
		$find = array();
		$replace = array();

		foreach ($replacements as $f => $r)
		{
			$find[] = '{' . $f . '}';
			$replace[] = $r;
		}

		/* Do the variable replacements. */
		return str_replace($find, $replace, self::tools()->getSetting('custom_message'));
	}

	public static function tools()
	{
		global $sourcedir;

		require_once($sourcedir. '/Subs-PostLimit.php');

		return PostLimitTools::getInstance();
	}

	protected function db($table)
	{
		global $sourcedir;

		require_once($sourcedir. '/Subs-PostLimit.php');

		return new PostLimitDB($table);
	}

	/* Permissions */
	public static function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionList['membergroup']['PostLimit_can_set_post_limit'] = array(false, 'PostLimit_per_classic', 'PostLimit_per_simple');
		$permissionGroups['membergroup']['simple'] = array('PostLimit_per_simple');
		$permissionGroups['membergroup']['classic'] = array('PostLimit_per_classic');
	}

	/* Profile hook */
	public static function profileHook(&$profile_areas)
	{
		global $sourcedir, $context;

		if (self::tools()->getSetting('enable'))
			$profile_areas['info']['areas']['userlimit'] = array(
				'label' => self::tools()->getText('profile_panel'),
				'file' => 'PostLimit.php',
				'function' => 'wrapper_profile_page',
				'permission' => array(
					'own' => 'PostLimit_can_set_post_limit',
					'any' => 'PostLimit_can_set_post_limit',
				),
			);
	}

	/* Profile page */
	public static function profilePage()
	{
		global $context, $user_info, $txt, $scripturl;

		loadtemplate('PostLimit');

			/* Set all the page stuff */
		$context['sub_template'] = 'postLimit_profile_page';
		$context += array(
			'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
		);
		$context['user']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
		$context['postLimit']['cannot'] = null;

		/* You cannot be here if you don't have the permission or if you are trying to set your own limit or if this user is an admin */
		if (!allowedTo('PostLimit_can_set_post_limit'))
			$context['postLimit']['cannot'] = self::tools()->getText('message_cannot');

		elseif ($context['member']['group_id'] == 1)
			$context['postLimit']['cannot'] = self::tools()->getText('message_cannot_admin');

		elseif ($context['user']['is_owner'])
			$context['postLimit']['cannot'] = self::tools()->getText('message_cannot_own');

		/* Get this user's limit */
		$pl_user = new PostLimit($context['member']['id']);
		$pl_limit = $pl_user->getLimit();
		$pl_count = $pl_user->getCount();
		$pl_boards = $pl_user->getBoards();

		$context['postLimit'] = array(
			'limit' => !empty($pl_limit) ? $pl_limit : 0,
			'count' => !empty($pl_count) ? $pl_count : 0,
			'boards' => !empty($pl_boards) ? $pl_limit : '',
		);

		if (isset($_GET['save']))
		{
			checkSession();

			if (isset($_REQUEST['postboards']))
				$_REQUEST['postboards'] = explode(',', preg_replace('/[^0-9,]/', '', $_REQUEST['postboards']));

			if (isset($_REQUEST['postlimit']))
				$_REQUEST['postlimit'] = preg_replace('/[^0-9,]/', '', $_REQUEST['postlimit']);

			/* Get the data */
			$data = array(
				'user' => $context['member']['id'],
				'limit' => (int) $_REQUEST['postlimit'],
				'boards' => implode(',', $_REQUEST['postboards'])
			);

			/* Update */
			if ($pl_user->rowExists())
				$pl_user->updateRow($data);

			/* Save */
			else
				$pl_user->createRow($data);

			redirectexit('action=profile;area=userlimit;u='. $context['member']['id'] .'');
		}
	}

	/* Admin menu hook */
	public static function admin(&$admin_areas)
	{
		$admin_areas['config']['areas']['postlimit'] = array(
					'label' => self::tools()->getText('admin_panel'),
					'file' => 'PostLimit.php',
					'function' => 'wrapper_admin_dispatch',
					'icon' => 'posts.gif',
					'subsections' => array(
						'general' => array(self::tools()->getText('admin_panel_settings')),
				),
		);
	}

	/* The settings hook */
	public static function settingsDispatch($return_config = false)
	{
		global $scripturl, $context, $sourcedir;

		require_once($sourcedir.'/ManageSettings.php');

		$context['page_title'] = self::tools()->getText('admin_panel');

		$subActions = array(
			'general' => 'wrapper_admin_settings',
		);

		loadGeneralSettingParameters($subActions, 'general');

		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => self::tools()->getText('admin_panel'),
			'description' => self::tools()->getText('admin_panel_desc'),
			'tabs' => array(
				'general' => array(),
			),
		);

		$subActions[$_REQUEST['sa']]();
	}

	/* Settings */
	static function settings()
	{
		global $scripturl, $context, $sourcedir;

		require_once($sourcedir.'/ManageServer.php');

		$config_vars = array(
			array('check', 'PostLimit_enable','subtext' => self::tools()->getText('enable_sub')),
			array('large_text', 'PostLimit_custom_message', '6" style="width:95%'),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=postlimit;sa=basic;save';

		/* Saving? */
		if (isset($_GET['save']))
		{
			checkSession();

			/* Force a new instance */
			self::tools()->__destruct();

			saveDBSettings($config_vars);
			redirectexit('action=admin;area=postlimit;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}
}