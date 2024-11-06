<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
    require_once(dirname(__FILE__) . '/SSI.php');
} elseif (!defined('SMF')) {
    exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

global $smcFunc, $context, $db_prefix;

db_extend('packages');

if (!empty($context['uninstalling'])) {
    return;
}

$table = [
    'table_name' => 'post_limit',
    'columns' => [
        [
            'name' => 'id_user',
            'type' => 'int',
            'size' => 5,
            'null' => false,
        ],
        [
            'name' => 'id_boards',
            'type' => 'varchar',
            'size' => 255,
            'default' => '',
        ],
        [
            'name' => 'post_limit',
            'type' => 'int',
            'size' => 5,
            'null' => false,
        ],
        [
            'name' => 'post_count',
            'type' => 'int',
            'size' => 5,
            'null' => false,
        ],
    ],
    'indexes' => [
        [
            'type' => 'primary',
            'columns' => ['id_user']
        ],
    ],
    'if_exists' => 'ignore',
    'error' => 'fatal',
    'parameters' => [],
];

$smcFunc['db_create_table']($db_prefix . $table['table_name'],
    $table['columns'],
    $table['indexes'],
    $table['parameters'],
    $table['if_exists'],
    $table['error']
);

/* Add the Scheduled Task */
$smcFunc['db_insert'](
    'ignore',
    '{db_prefix}scheduled_tasks',
    [
        'id_task' => 'int',
        'next_time' => 'int',
        'time_offset' => 'int',
        'time_regularity' => 'int',
        'time_unit' => 'string',
        'disabled' => 'int',
        'task' => 'string',
    ],
    // Directly call the repository FTW!!!!
    [0,0,0,1,'d',0,'$sourcedir/PostLimit/PostLimitRepository.php|\PostLimit\PostLimitRepository::resetPostCount#'],
    ['task']
);