<?php

/**
 * Post Limit mod (SMF)
 *
 * @package SMF
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2019 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.1
 */

if (!defined('SMF')) {
    die('Hacking attempt...');
}

class PostLimitDB
{
    public function __construct($table)
    {
        $this->table = isset($table) ? '{db_prefix}'.$table : null;
        $this->data_result = array();
    }

    public function params($params, $data = null, $values = null)
    {
        if (is_null($params)) {
            return false;
        }

        $this->rows = isset($params['rows']) ? trim($params['rows']) : null;
        $this->where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
        $this->whereAnd = isset($params['and']) ? 'AND '.trim($params['and']) : null;
        $this->limit = isset($params['limit']) ? 'LIMIT '.trim($params['limit']) : null;
        $this->left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;
        $this->order = isset($params['order']) ? 'ORDER BY '.trim($params['order']) : null;
        $this->set = isset($params['set']) ? 'SET '.trim($params['set']) : null;
        $this->data = !is_array($data) ? array($data) : $data;
    }

    public function getData($key = null, $single = false)
    {
        global $smcFunc;

        if ($key) {
            $this->key = $key;
        }

        $query = $smcFunc['db_query'](
            '',
            '
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

        if (!$query) {
            $this->data_result = array();
        }

        if ($single) {
            while ($row = $smcFunc['db_fetch_assoc']($query)) {
                $this->data_result = $row;
            }
        }

        if ($key) {
            while ($row = $smcFunc['db_fetch_assoc']($query)) {
                $this->data_result[$row[$this->key]] = $row;
            }
        } else {
            while ($row = $smcFunc['db_fetch_assoc']($query)) {
                $this->data_result[] = $row;
            }
        }

        $smcFunc['db_free_result']($query);

        /* return $this->data_result; */
    }

    public function dataResult()
    {
        return $this->data_result;
    }

    public function updateData()
    {
        global $smcFunc;

        $smcFunc['db_query'](
            '',
            '
			UPDATE '.$this->table .'
			'.$this->set .'
			'.$this->where .'
			'.$this->order .'
			'.$this->limit .'
			',
            $this->data
        );
    }

    public function deleteData()
    {
        global $smcFunc;

        $smcFunc['db_query'](
            '',
            '
			DELETE FROM '.$this->table .'
			'.$this->where .'
			'.$this->order .'
			'.$this->limit .'
			',
            $this->data
        );
    }

    public function insertData($data, $values, $indexes)
    {
        if (is_null($values) || is_null($indexes) || is_null($data)) {
            return false;
        }

        global $smcFunc;

        $this->indexes = isset($params['indexes']) ? array($params['indexes']) : null;
        $this->values = !is_array($values) ? array($values) : $values;
        $this->data = !is_array($data) ? array($data) : $data;

        $smcFunc['db_insert'](
            'replace',
            ''.$this->table .'',
            $this->data,
            $this->values,
            $this->indexes
        );
    }

    public function count($params = null, $data = null)
    {
        global $smcFunc;

        if (is_null($params)) {
            $params = array();
        }

        if (is_null($data)) {
            $data = array();
        }

        $this->data = !is_array($data) ? array($data) : $data;
        $this->where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
        $this->left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;

        $request = $smcFunc['db_query'](
            '',
            '
			SELECT COUNT(*)
			FROM '.$this->table .'
			' . $this->where . '
			' . $this->left . '
			',
            $this->data
        );

        list($count) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        return $count;
    }
}

class PostLimitTools
{
    protected static $_instance;
    protected $_settings;
    protected $_text;
    protected $_name = 'PostLimit';

    /**
     * Initialize the extract() method and sets the pattern property using $_name's value.
     *
     * @access protected
     * @return void
     */
    protected function __construct()
    {
        /* Set the pattern property with $_name's value */
        $this->_pattern = '/'. $this->_name .'_/';

        /* Extract the requested values from the arrays */
        $this->extract();
    }

    /**
     * Set's a unique instance for the class.
     *
     * @access public
     * @return object
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Extracts the requested values form the $modSettings and txt arrays, sets $_text and $_settings with the founded data.
     *
     * @global array $modSettings SMF's modSettings variable
     * @global array $txt SMF's text strings
     * @access public
     * @return void
     */
    public function extract()
    {
        global $modSettings, $txt;

        /* Load the mod's language file */
        loadLanguage($this->_name);

        $this->_settings = $modSettings;

        $this->_text = $txt;
    }

    /**
     * Return true if the param value do exists on the $_settings array, false otherwise.
     *
     * @param string the name of the key
     * @access public
     * @return bool
     */
    public function enable($var)
    {
        if (!empty($this->_settings[$this->_name .'_'. $var])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the requested array element.
     *
     * @param string the key name for the requested element
     * @access public
     * @return mixed
     */
    public function getSetting($var)
    {
        if (empty($var)) {
            return false;
        } elseif (!empty($this->_settings[$this->_name .'_'. $var])) {
            return $this->_settings[$this->_name .'_'. $var];
        } else {
            return false;
        }
    }

    /**
     * Get the requested array element.
     *
     * @param string the key name for the requested element
     * @access public
     * @return mixed
     */
    public function getText($var)
    {
        if (!empty($this->_text[$this->_name .'_'. $var])) {
            return $this->_text[$this->_name .'_'. $var];
        } else {
            return false;
        }
    }
}
