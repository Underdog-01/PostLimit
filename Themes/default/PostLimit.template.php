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

use PostLimit\PostLimit;

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
                    name="', \PostLimit\PostLimitEntity::POST_LIMIT ,'" 
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
                    name="', \PostLimit\PostLimitEntity::ID_BOARDS ,'" 
                    size="50" 
                    tabindex="1" 
                    maxlength="25" 
                    value="', implode(',', $context[PostLimit::NAME]->getIdBoards()),'" />
            </dd>
        </dl>
        <input type="submit" name="save" value="', $txt['save'] ,'" class="button floatright">
        <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
    </form>';
}
