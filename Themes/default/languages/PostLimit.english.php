<?php

/**
 * Post Limit mod (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2025 Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.1
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

/* Admin panel */
$txt['PostLimit_message_default'] = 'Hi {username}, you have reached your post limit for today.';
$txt['PostLimit_admin_panel'] = 'Post Limit admin panel';
$txt['PostLimit_admin_panel_desc'] = 'From here you can set some global settings and permissions for Post Limit mod';
$txt['PostLimit_admin_settings'] = 'Settings';
$txt['PostLimit_admin_permissions'] = 'Permissions';
$txt['PostLimit_enable'] = 'Enable Post Limit Modification';
$txt['PostLimit_post_count_alert'] = 'Post Percentage Limit Alert';
$txt['PostLimit_post_count_alert_help'] = '
<div style="font-size: smaller;padding-bottom: 1rem;">
	When the user has reached [X] percentage of posts before hitting the post limit, an alert will be sent giving indication that they have [X] amount of posts left.
	<div>
		This setting is based on a percentage value due to variable post limits per member.
	</div>
</div>
<div style="font-size: smaller;padding-bottom: 1rem;">
	<div>
		For example, if you configure this setting to 80%, and a member has a 10 post limit, the member will receive an alert on his/her 8th post.
	</div>
	<div>
		This will give them an indicatation that they only have 2 more posts left (default is 80).
	</div>
</div>';
$txt['PostLimit_enable_help'] = '
<div style="font-size: smaller;">
	This setting must be enabled for this modification to function.
</div>';
$txt['PostLimit_custom_message'] = 'Custom Message';
$txt['PostLimit_custom_message_help'] = '
<div style="font-size: smaller;padding-bottom: 1rem;">
	Write a custom message for members that have reached their posting limit.
</div>
<div style="font-size: smaller;padding-bottom: 1rem;">
	You can use the following placeholder tokens to personalize the message even more:
	<div>
		- {username} will display the display name of the user who will receive the message
	</div>
	<div>
		- {limit} will display the amount of messages this particular user can make
	</div>
</div>
<div style="font-size: smaller;padding-bottom: 1rem;">
	If you leave this message empty, the default message will appear:<br><i>'. $txt['PostLimit_message_default'] .'</i>
</div>';
$txt['PostLimit_alert_message_default'] = 'Hi {username}! you have reached {percentage}% of the total amount you are allowed to post for today.';
$txt['PostLimit_custom_alert_message'] = 'Custom Alert Message';
$txt['PostLimit_custom_alert_message_help'] = '
<div style="font-size: smaller;padding-bottom: 1rem;">
	Create a custom alert message which the user will see when they have reached their "Post Percentage Limit".
	<div>
		You can use the following placeholder tokens to personalize the message even more:
	</div>
	<div>
		- {username} will display the display name of the user who will receive the message
	</div>
	<div>
		- {limit} will display the amount of messages this particular user can make.
	</div>
	<div>
		- {percentage} will show the percentage the user has reached.
	</div>
	<div>
		- {post_left} The amount of posts the user can make before reaching their limit.
	</div>
</div>
<div style="font-size: smaller;padding-bottom: 1rem;">
	If you leave this message empty, the default message will appear:<br><i>'. $txt['PostLimit_alert_message_default'] .'</i>
</div>';
$txt['PostLimit_profile_panel'] = 'Post Limit profile panel';
$txt['PostLimit_profile_panel_sub'] = 'You can set the post limit and the boards this limit will be applied for this user here';

/* Messages */
$txt['PostLimit_message_overlimit'] = 'You have already exceeded the daily post limit, your limit is: %d';
$txt['PostLimit_message'] = 'You have %d messages remaining in your post limit for today';
$txt['PostLimit_message_title'] = 'Attention %s!';

/* Profile fields */
$txt['PostLimit_profile_userlimit'] = 'Post Limit';
$txt['PostLimit_profile_userlimit_desc'] = 'You can put any number, if empty, this user will not be limited.';
$txt['PostLimit_profile_boards'] = 'Board IDs';
$txt['PostLimit_profile_boards_desc'] = 'Write the board Id\'s where this user will be limited, comma separated, example: 1,2,3,4
<br> If leave empty, and a post limit is set, the user will be limited on all boards';

/* Permissions strings */
$txt['cannot_can_set_post_limit'] = 'I\'m sorry, you are not allowed to set post limits.';
$txt['permissiongroup_simple_PostLimit_per_simple'] =
$txt['permissiongroup_PostLimit_per_classic']  =
$txt['PostLimit_permissions_title'] = 'Post Limit mod permissions';
$txt['PostLimit_can_set_post_limit'] = $txt['permissionname_PostLimit_can_set_post_limit'] = 'Can set post limits';
$txt['PostLimit_message_cannot'] = $txt['cannot_can_set_post_limit'];
$txt['PostLimit_message_cannot_admin'] = 'Admins cannot be limited';
$txt['PostLimit_message_cannot_own'] = 'You cannot set your own limit';
$txt['PostLimit_message_cannot_general'] = 'Post Limit warning: %s';

/* Scheduled Task */
$txt['scheduled_task_post_limit'] = 'Post Limit mod';
$txt['scheduled_task_desc_post_limit'] = 'Resets the post limit of every user down to 0.';

// Alert text
$txt['PostLimit_alert_text'] = 'You have reached {percentage}% of your {limit} {frequency} limit. You have {postsLeft} remaining.';
$txt['PostLimit_alert_frequency'] = 'daily posts';
$txt['PostLimit_alert_text_limit_reached'] = 'You have reached your ' . $txt['PostLimit_alert_frequency'] . ' limit.';

// Uninstall all data warning
$txt['PostLimit_uninstall_db'] = 'Removes all database entries made by Post Limit';
$txt['PostLimit_uninstall_files'] = 'Removes all files and folders including custom images for Post Limit';
$txt['PostLimit_uninstall_warning'] = 'Activating the above checkbox will perform these actions and is not reversable!';