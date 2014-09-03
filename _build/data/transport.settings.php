<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * MODXTalks is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MODXTalks is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * @var modX $modx
 * @package modxtalks
 * @subpackage build
 */
$settings = [];

$settings['modxtalks.emailsFrom'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.emailsFrom']->fromArray([
	'key' => 'modxtalks.emailsFrom',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'emailNotifications',
], '', true, true);

$settings['modxtalks.emailsReplyTo'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.emailsReplyTo']->fromArray([
	'key' => 'modxtalks.emailsReplyTo',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'emailNotifications',
], '', true, true);

$settings['modxtalks.highlight'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.highlight']->fromArray([
	'key' => 'modxtalks.highlight',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'highlight',
], '', true, true);

$settings['modxtalks.highlighttheme'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.highlighttheme']->fromArray([
	'key' => 'modxtalks.highlighttheme',
	'value' => 'GitHub',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'highlight',
], '', true, true);

$settings['modxtalks.smileys'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.smileys']->fromArray([
	'key' => 'modxtalks.smileys',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settingBBCode',
], '', true, true);

$settings['modxtalks.editOptionsControls'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.editOptionsControls']->fromArray([
	'key' => 'modxtalks.editOptionsControls',
	'value' => 'fixed,image,link,strike,header,italic,bold,video,quote',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settingBBCode',
], '', true, true);

$settings['modxtalks.detectUrls'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.detectUrls']->fromArray([
	'key' => 'modxtalks.detectUrls',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settingBBCode',
], '', true, true);

$settings['modxtalks.bbcode'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.bbcode']->fromArray([
	'key' => 'modxtalks.bbcode',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settingBBCode',
], '', true, true);

$settings['modxtalks.voting'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.voting']->fromArray([
	'key' => 'modxtalks.voting',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.revers'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.revers']->fromArray([
	'key' => 'modxtalks.revers',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.scrubberTop'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.scrubberTop']->fromArray([
	'key' => 'modxtalks.scrubberTop',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.scrubberOffsetTop'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.scrubberOffsetTop']->fromArray([
	'key' => 'modxtalks.scrubberOffsetTop',
	'value' => 0,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.preModarateComments'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.preModarateComments']->fromArray([
	'key' => 'modxtalks.preModarateComments',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.onlyAuthUsers'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.onlyAuthUsers']->fromArray([
	'key' => 'modxtalks.onlyAuthUsers',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.moderator'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.moderator']->fromArray([
	'key' => 'modxtalks.moderator',
	'value' => 'Administrator',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.gravatarSize'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.gravatarSize']->fromArray([
	'key' => 'modxtalks.gravatarSize',
	'value' => 64,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.gravatar'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.gravatar']->fromArray([
	'key' => 'modxtalks.gravatar',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.edit_time'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.edit_time']->fromArray([
	'key' => 'modxtalks.edit_time',
	'value' => 180,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.defaultAvatar'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.defaultAvatar']->fromArray([
	'key' => 'modxtalks.defaultAvatar',
	'value' => '',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.dateFormat'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.dateFormat']->fromArray([
	'key' => 'modxtalks.dateFormat',
	'value' => 'j-m-Y, G:i',
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.commentsPerPage'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.commentsPerPage']->fromArray([
	'key' => 'modxtalks.commentsPerPage',
	'value' => 20,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.commentLength'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.commentLength']->fromArray([
	'key' => 'modxtalks.commentLength',
	'value' => 2000,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.ajax'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.ajax']->fromArray([
	'key' => 'modxtalks.ajax',
	'value' => true,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.add_timeout'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.add_timeout']->fromArray([
	'key' => 'modxtalks.add_timeout',
	'value' => 60,
	'xtype' => 'textfield',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

$settings['modxtalks.fullDeleteComment'] = $modx->newObject('modSystemSetting');
$settings['modxtalks.fullDeleteComment']->fromArray([
	'key' => 'modxtalks.fullDeleteComment',
	'value' => false,
	'xtype' => 'combo-boolean',
	'namespace' => 'modxtalks',
	'area' => 'settings',
], '', true, true);

return $settings;
