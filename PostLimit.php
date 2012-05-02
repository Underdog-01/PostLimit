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

class PostLimit
{
	private $_user;
	private $_board;
	private $_tools;
	private $_params = array();
	private $_data = array();
	static private $_dbTableName = 'post_limit';

	public function __construct($user, $board)
	{
		if (empty($user) || empty($board))
			return false;

		$this->_user = $user;
		$this->_board = $board;
		$this->_tools = $this->tools();
		$this->_db = $this->db(self::$_dbTableName);
		$this->_params = array(
			'where' => 'id_user = {int:id_user}'
		);
		$this->_data = array(
			'id_user' => $this->_user
		);
	}

	public function updateCount()
	{
		/* Update! */
		$this->_params['set'] = 'post_count=post_count+1';
		$this->_db->params($this->_params, $this->_data);
		$this->_db->updateData();
	}

	public function getCount()
	{
		$this->_params['rows'] = 'post_count';
		$this->_db->params($this->_params, $this->_data);

		return $this->_db->getData(null, true);
	}

	public function isBoardLimited()
	{

	}

	public function tools()
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
}