<?php
/**
 * MODXTalks
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>. Translation by Viktorminator
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 *
 */
/**
 * MODXTalks properties English language file
 *
 * @package modxtalks
 * @subpackage lexicon
 */

/* options */
$_lang['area'] = 'Section';
$_lang['area_all'] = 'General';
$_lang['area_settings'] = 'General Settings';
$_lang['area_temlates'] = 'Templates';
$_lang['area_comment'] = 'Comment';

/* MODXTalks */
$_lang['modxtalks.prop_conversation_desc'] = 'Unique name for the block outputs a comment by default <code><span style="color: #009900;">[</span><span style="color: #009900;">[</span><span style="color: #339933;">*</span>class_key<span style="color: #009900;">]</span><span style="color: #009900;">]</span><span style="color: #339933;">-</span><span style="color: #009900;">[</span><span style="color: #009900;">[</span><span style="color: #339933;">*</span>id<span style="color: #009900;">]</span><span style="color: #009900;">]</span></code>';
$_lang['modxtalks.prop_moderator_desc'] = 'Moderator groups separated by commas.';
$_lang['modxtalks.prop_onlyAuthUsers_desc'] = 'Only authorised users. Comments can be left only by authorised users.';
$_lang['modxtalks.prop_gravatar_desc'] = 'Use Gravatar service. Enable Gravatar image in user messages. Yes/No';
$_lang['modxtalks.prop_gravatarSize_desc'] = 'Gravatar size in the comment.';
$_lang['modxtalks.prop_defaultAvatar_desc'] = 'Default avatar Full link to avatar which appeared when Gravatar is disabled or doesn`t exist.';
$_lang['modxtalks.prop_dateFormat_desc'] = 'Date format j-m-Y, G:i. Date format displayed in a comment.';
$_lang['modxtalks.prop_commentsPerPage_desc'] = 'Max number of comments on page for pagination.';
$_lang['modxtalks.prop_add_timeout_desc'] = 'The time in seconds between sending comments.';
$_lang['modxtalks.prop_edit_time_desc'] = 'Time for editing. Time after which user can`t edit his comment';
$_lang['modxtalks.prop_commentsClosed_desc'] = 'Disallow comments to all but selected group moderators. Comments are closed.';
$_lang['modxtalks.prop_commentLength_desc'] = 'Comment length. Max length of comments (in symbols).';
$_lang['modxtalks.prop_commentTpl_desc'] = 'Template for comments.';
$_lang['modxtalks.prop_deletedCommentTpl_desc'] = 'Template for deleting comments.';
$_lang['modxtalks.prop_commentAddFormTpl_desc'] = 'The form template add a comment.';
$_lang['modxtalks.prop_commentEditFormTpl_desc'] = 'The form template editing comments.';
$_lang['modxtalks.prop_commentAuthTpl_desc'] = 'Pattern registration form. Output is limited to authorized users, if the parameter is active onlyAuthUsers.';

/* modxTalksLatestComments */
$_lang['modxtalks.prop_commentLatestTpl_desc'] = 'Template separate comment for the block. Maybe chunk name. If the set value, it will replace the original template.';
$_lang['modxtalks.prop_commentsLatestOutTpl_desc'] = 'Shell pattern for the block comment. Maybe chunk name. If the set value, it will replace the original template.';
$_lang['modxtalks.prop_commentsLatestLimit_desc'] = 'Restrict the output of comments per page.';