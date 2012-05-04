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

class PostLimit
{
	private $_user;
	private $_board;
	public static $tools;
	private $_params = array();
	private $_data = array();
	private $_rows = array();
	static private $_dbTableName = 'post_limit';

	public function __construct($user, $board)
	{
		if (empty($user) || empty($board))
			return false;

		$this->_user = $user;
		$this->_board = $board;
		$this->_db = $this->db(self::$_dbTableName);
		$this->_params = array(
			'where' => 'id_user = {int:id_user}'
		);
		$this->_data = array(
			'id_user' => $this->_user
		);
		$this->_rows = array(
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

		return $this->_db->getData(null, true);
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
		$permissionList['membergroup']['PostLimit_edit_settings_any'] = array(false, 'PostLimit_per_classic', 'PostLimit_per_simple');
		$permissionGroups['membergroup']['simple'] = array('PostLimit_per_simple');
		$permissionGroups['membergroup']['classic'] = array('PostLimit_per_classic');
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
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=postlimit;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}
}