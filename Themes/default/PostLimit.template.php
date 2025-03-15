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


use PostLimit\PostLimit;
use PostLimit\PostLimitEntity as PostLimitEntity;

if (!defined('SMF')) {
	die('Hacking attempt...');
}

function template_postLimit_profile_page()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg profile_hd">
			', $txt['PostLimit_profile_panel'] ,'
		</h3>
	</div>
	<p class="information">
		', $txt['PostLimit_profile_panel_sub'] ,'
	</p>
	<div class="roundframe">
		<form
			action="', $scripturl , '?action=profile;area=', strtolower(PostLimit::NAME) ,';u=', $context['member']['id'] ,';save"
			method="post"
			accept-charset="UTF-8"
			name="creator"
			id="creator"
			enctype="multipart/form-data"
			target="_self">
			<dl class="settings">
				<dt>
					<strong>', $txt['PostLimit_profile_userlimit'] ,'</strong><br />
					<span class="smalltext">', $txt['PostLimit_profile_userlimit_desc'] ,'</span>
				</dt>
				<dd>
					<input
						type="text"
						name="', PostLimitEntity::POST_LIMIT ,'"
						size="5"
						tabindex="1"
						maxlength="5"
						value="', $context[PostLimit::NAME]->getPostLimit() ,'" />
				</dd>
				<dt>
					<strong>', $txt['PostLimit_profile_boards'] ,'</strong><br />
					<span class="smalltext">', $txt['PostLimit_profile_boards_desc'] ,'</span>
				</dt>
				<dd>
					<input type="text"
						name="', PostLimitEntity::ID_BOARDS ,'"
						size="50"
						tabindex="1"
						maxlength="25"
						value="', implode(',', $context[PostLimit::NAME]->getIdBoards()),'" />
				</dd>
			</dl>
			<input type="submit" name="save" value="', $txt['save'] ,'" class="button floatright">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}
