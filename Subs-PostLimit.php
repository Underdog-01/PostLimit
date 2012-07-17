<?php

/**
 * Post Limit mod (SMF)
 *
 * @package SMF
 * @author Suki <missallsunday@simplemachines.org>
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

class PostLimitDB
{
	function __construct($table)
	{
		$this->table = isset($table) ? '{db_prefix}'.$table : null;
		$this->data_result = array();
	}

	function params($params, $data = null, $values = null)
	{
		if(is_null($params))
			return false;

		$this->rows = isset($params['rows']) ? trim($params['rows']) : null;
		$this->where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
		$this->whereAnd = isset($params['and']) ? 'AND '.trim($params['and']) : null;
		$this->limit = isset($params['limit']) ? 'LIMIT '.trim($params['limit']) : null;
		$this->left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;
		$this->order = isset($params['order']) ? 'ORDER BY '.trim($params['order']) : null;
		$this->set = isset($params['set']) ? 'SET '.trim($params['set']) : null;
		$this->data = !is_array($data) ? array($data) : $data;
	}

	function getData($key = null, $single = false)
	{
		global $smcFunc;

		if ($key)
			$this->key = $key;

		$query = $smcFunc['db_query']('', '
			SELECT '.$this->rows .'
			FROM '.$this->table .'
			'. $this->left .'
			'. $this->where .'
			'. $this->whereAnd .'
			'. $this->order .'
			'. $this->limit .'
			',
			$this->data
		);

		if (!$query)
			$this->data_result = array();

		if($single)
			while ($row = $smcFunc['db_fetch_assoc']($query))
				$this->data_result = $row;

		if ($key)
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->data_result[$row[$this->key]] = $row;

		else
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->data_result[] = $row;

		$smcFunc['db_free_result']($query);

		/* return $this->data_result; */
	}

	function dataResult()
	{
		return $this->data_result;
	}

	function updateData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			UPDATE '.$this->table .'
			'.$this->set .'
			'.$this->where .'
			'.$this->order .'
			'.$this->limit .'
			',
			$this->data
		);
	}

	function deleteData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM '.$this->table .'
			'.$this->where .'
			'.$this->order .'
			'.$this->limit .'
			',
			$this->data
		);
	}

	function insertData($data, $values, $indexes)
	{
		if(is_null($values) || is_null($indexes) || is_null($data))
			return false;

		global $smcFunc;

		$this->indexes = isset($params['indexes']) ? array($params['indexes']) : null;
		$this->values = !is_array($values) ? array($values) : $values;
		$this->data = !is_array($data) ? array($data) : $data;

		$smcFunc['db_insert']('replace',
			''.$this->table .'',
			$this->data ,
			$this->values ,
			$this->indexes
		);
	}

	function count($params = null, $data = null)
	{
		global $smcFunc;

		if(is_null($params))
			$params = array();

		if(is_null($data))
			$data = array();

		$this->data = !is_array($data) ? array($data) : $data;
		$this->where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
		$this->left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM '.$this->table .'
			' . $this->where . '
			' . $this->left . '
			',
			$this->data
		);

		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $count;
	}
}

class PostLimitTools
{
	private static $_instance;
	private $_settings;
	private $_text;

	private function __construct()
	{
		$this->doExtract();
	}

	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new PostLimitTools();
		}
		return self::$_instance;
	}

	public function doExtract()
	{
		global $txt, $modSettings;

		loadLanguage('PostLimit');

		$this->pattern = '/PostLimit_/';

		/* Get only the settings that we need */
		if (($this->_settings = cache_get_data(PostLimit::$name':settings', 360)) == null)
		{
			foreach ($modSettings as $km => $vm)
				if (preg_match($this->pattern, $km))
				{
					$km = $this->replace($km);

					/* Populate the new array */
					$this->_settings[$km] = $vm;
				}

			cache_put_data(PostLimit::$name':settings', $this->_settings, 360);
		}

		/* Again, this time for $txt. */
		if (($this->_text = cache_get_data(PostLimit::$name':text', 360)) == null)
		{
			foreach ($txt as $kt => $vt)
				if (preg_match($this->pattern, $kt))
				{
					$kt = $this->replace($kt);
					$this->_text[$kt] = $vt;
				}

			cache_put_data(PostLimit::$name':text', $this->_settings, 360);
		}

		/* Done? then we don't need this anymore */
		if (!empty($this->_text) && !empty($this->_settings))
		{
			unset($this->matchesText);
			unset($this->matchesSettings);
		}
	}

	private function replace($var)
	{
		return $var = str_replace('PostLimit_', '', $var);
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enable($var)
	{
		if (!empty($this->_settings[$var]))
			return true;
		else
			return false;
	}

	/* Get the requested setting  */
	public function getSetting($var)
	{
		if (!empty($this->_settings[$var]))
			return $this->_settings[$var];

		else
			return false;
	}

	public function getText($var)
	{
		if (!empty($this->_text[$var]))
			return $this->_text[$var];

		else
			return false;
	}

	public function __destruct() {}
}