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
use PostLimit\PostLimitUtils as PostLimitUtils;

class PostLimit
{
	public const NAME = 'PostLimit';
	public const DEFAULT_PERCENTAGE_TO_ALERT = 80;
	public const DEFAULT_POST_LIMIT = 0;
	protected PostLimitService $service;

	public function __construct(?PostLimitService $service = null)
	{
		//No DI :(
		$this->getClasses();
		$this->removeAllDataInfo();
		$this->service = $service ?? new PostLimitService();
	}

	public function s(): bool
	{
		global $sourcedir;

		$repository = new PostLimitRepository();
		$repository->resetPostCount();

		// Make sure we are the last hook call, don't ask, just sage nod and move on
		$this->reOrderHookCall($repository);

		//No point in keeping alerts
		$repository->deleteAllAlerts();
		return true;
	}

	public function allowGeneral(&$user_permissions, array $permission)
	{
		global $user_info, $board_info;

		$permissionName = $permission[0];

		if (!$this->service->isEnable() || !isset($board_info) || !$this->service->checkPermissions($permissionName)) {
			return;
		}

		$entity = $this->service->getEntityByUser((int) $user_info['id']);

		if ($this->service->isLimitReachedByUser($entity, (int) $board_info['id'])) {
			$key = array_search($permissionName, $user_permissions);
			unset($user_permissions[$key]);
		}
	}

	public function checkLimit($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
	{
		$posterId = (int) $posterOptions['id'];
		$boardId = (int) $topicOptions['board'];
		$entity = $this->service->getEntityByUser($posterId);

		if ($this->service->isLimitReachedByUser($entity, $boardId)) {
			$errorMessage = $this->service->buildErrorMessage($entity, $posterOptions['name']);

			fatal_lang_error($errorMessage);
		}
	}

	public function checkAlert($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
	{
		$posterId = (int) $posterOptions['id'];

		$this->service->updateCount($posterId);
		$entity = $this->service->getEntityByUser($posterId);

		$this->service->buildAlert($entity);
	}

	public function createCount(&$regOptions, &$theme_vars, &$memberID)
	{
		$this->service->createDefaultEntity((int) $memberID);
	}

	public static function loadLanguage(): void
	{
		global $txt;

		loadLanguage('PostLimit');
	}

	public static function getClasses(): void
	{
		// autoload class helper function
		global $sourcedir;

		spl_autoload_register(
			function ($class) use ($sourcedir){
				if (strpos($class, "PostLimit\\") !== 0) {
					return;
				}
				$file = $sourcedir . '/' . str_replace('\\', '/', $class) . '.php';
				if (is_file($file)) {
					require_once $file;
				}
			}
		);
	}

	public static function removeAllDataInfo(): void
	{
		global $txt, $context;

		// custom remove all data info
		foreach(array('action', 'area', 'sa', 'package') as $request) {
			$$request = !empty($_REQUEST[$request]) && is_string($_REQUEST[$request]) ? $_REQUEST[$request] : '';
		}
		$actionCheck = stripos($action, 'admin;area=packages;sa=uninstall;package=') !== FALSE && stripos($action, 'postlimit_v') !== FALSE;
		if ((array($action, $area, $sa) == array('admin', 'packages', 'uninstall') && stripos($package, 'postlimit_v') !== FALSE) || $actionCheck) {
			$lang = new PostLimitUtils();
			$context['html_headers'] .= '
			<script>
				$(document).ready(function(){
					$("#db_changes_div > ul.normallist").append("<li>' . $lang->text('uninstall_db') . '</li>");
					$("#db_changes_div > ul.normallist").append("<li>' . $lang->text('uninstall_files') . '</li>");
					$("#db_changes_div").append(\'<span style="font-weight: bold;">' . $lang->text('uninstall_warning') . '</span>\');
				});
			</script>';
		}
	}

	protected function reOrderHookCall(PostLimitRepository $repository): void
	{
		$hookReference = 'PostLimit\PostLimit::checkLimit';
		$hooks = $repository->getCreatePostHooks();
		$explodedHooks = explode(',', $hooks);

		if (count($explodedHooks) <= 1) {
			return;
		}

		$key = array_search($hookReference, $explodedHooks, true);

		if ($key === false) {
			return;
		}

		unset($explodedHooks[$key]);
		$explodedHooks[] = $hookReference;

		$repository->updateCreatePostHooks(trim(implode(',', array_map('trim', $explodedHooks))));
	}
}