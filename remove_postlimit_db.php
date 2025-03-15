<?php

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $smcFunc;

db_extend('packages');

// Don't forget to remove the scheduled task...
$smcFunc['db_query']('', "DELETE FROM {db_prefix}scheduled_tasks WHERE task LIKE 'post_limit'");

remove_integration_function('integrate_admin_areas', 'PostLimit\PostLimitAdmin::menu#');
remove_integration_function('integrate_load_permissions', 'PostLimit\PostLimitAdmin::permissionsHook#');
remove_integration_function('integrate_create_post', 'PostLimit\PostLimit::checkLimit#');
remove_integration_function('integrate_after_create_post', 'PostLimit\PostLimit::checkAlert#');
remove_integration_function('integrate_fetch_alerts', 'PostLimit\PostLimitAlerts::handle#');
remove_integration_function('integrate_allowed_to_general', 'PostLimit\PostLimit::allowGeneral#');
remove_integration_function('integrate_post_register', 'PostLimit\PostLimit::createCount#');
remove_integration_function('integrate_profile_areas', 'PostLimit\PostLimitProfile::setArea#');
remove_integration_function('integrate_pre_load', '$sourcedir/PostLimit/PostLimit.php|PostLimit\PostLimit::loadLanguage#|PostLimit\PostLimit::getClasses#');

if ($smcFunc['db_list_tables'](false, '{db_prefix}post_limit')) {
	$smcFunc['db_drop_table']('{db_prefix}post_limit');
}

?>