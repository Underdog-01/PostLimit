<?php

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

// Don't forget to remove the scheduled task...
$smcFunc['db_query']('', "DELETE FROM {db_prefix}scheduled_tasks WHERE task LIKE 'postLimit'");
