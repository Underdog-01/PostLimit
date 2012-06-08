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

function template_postLimit_profile_page()
{
	global $txt, $context, $scripturl;

	if (!empty($context['postLimit']['cannot']))
		echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							', $context['postLimit']['cannot'] ,'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
	else
	{
				echo '
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">
							', $txt['PostLimit_profile_panel'] ,'
						</span>
					</h3>
				</div>';

				echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">';

				/* Form */
				echo '<form action="', $scripturl , '?action=profile;area=userlimit;u=', $context['member']['id'] ,';save" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);" >
						<dl id="post_header">
							<dt>
								<span id="caption_subject">', $txt['PostLimit_profile_userlimit'] ,'</span>
							</dt>
							<dd>
								<input type="text" name="postlimit" size="5" tabindex="1" maxlength="5" value="', $context['postLimit']['limit'] ,'" class="input_text" /><br />
								', $txt['PostLimit_profile_userlimit_desc'] ,'
							</dd>
							<dt>
								<span id="caption_subject">', $txt['PostLimit_profile_boards'] ,'</span>
							</dt>
							<dd>
								<input type="text" name="postboards" size="25" tabindex="1" maxlength="25" value="', $context['postLimit']['boards'] ,'" class="input_text" /><br />
								', $txt['PostLimit_profile_boards_desc'] ,'
							</dd>
						</dl>
					<div id="confirm_buttons">
						<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="submit" name="send" class="sbtn" value="', $txt['PostLimit_profile_save'] ,'" />
					</div>';

				echo '
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
	}
}