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

 global $txt;

$txt['PostLimit_'] = '';

 /* Admin panel */
$txt['PostLimit_admin_panel'] = 'Post Limit admin panel';
$txt['PostLimit_admin_panel_settings'] = 'Settings';
$txt['PostLimit_admin_panel_desc'] = 'From here you can set some global settings for the mod';
$txt['PostLimit_enable'] = 'Enable the mod';
$txt['PostLimit_enable_sub'] = 'This Setting must be on for the mod to work properly.';
$txt['PostLimit_custom_message'] = '';
$txt['PostLimit_custom_message_sub'] = 'Write the cutom message the user will see, you can use {username} and {limit} to personalize the message even more<br />- {username} will display the nick of the user who will receive the message<br />- {limit} will display the amount of messages this particular user can made.';
$txt['PostLimit_profile_panel'] = 'Post Limit profile panel';

/* Messages */
$txt['PostLimit_message_1'] = 'You have 1 message left';
$txt['PostLimit_message_2'] = 'You have 2 messages left';
$txt['PostLimit_message_3'] = 'You have 3 messages left';

/* Profile fields */
$txt['PostLimit_profile_userlimit'] = 'Post Limit';
$txt['PostLimit_profile_userlimit_desc'] = 'You can put any number, if empty, this user will not have any limit.';
$txt['PostLimit_profile_boards'] = 'Board IDs';
$txt['PostLimit_profile_boards_desc'] = 'Write the board Id\'s where this user will be limited, comma separated, example: 1,2,3,4';
$txt['PostLimit_profile_save'] = 'Save';

/* Permissions strings */
$txt['cannot_can_set_post_limit'] = 'I\'m sorry, you are not allowed to set post limits.';
$txt['permissiongroup_PostLimit_per_simple'] = 'Post Limit mod permissions';
$txt['permissiongroup_PostLimit_per_classic'] = 'Post Limit mod permissions';
$txt['permissionname_PostLimit_can_set_post_limit'] = 'Can set post limits';
$txt['PostLimit_message_cannot'] = $txt['cannot_can_set_post_limit'];
$txt['PostLimit_message_cannot_admin'] = 'Admins cannot be limited';
$txt['PostLimit_message_cannot_own'] = 'You cannot set your own limit';

/* Scheduled Task */
$txt['scheduled_task_postLimit'] = 'Post Limit mod';
$txt['scheduled_task_desc_postLimit'] = 'Resets the post limit of every user down to 0.';
