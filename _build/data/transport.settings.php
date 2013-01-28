<?php
/**
 * modxTalks
 *
 * Copyright 2011-12 by Shaun McCormick <shaun@modx.com>
 *
 * modxTalks is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * modxTalks is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * modxTalks; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * @var modX $modx
 * @package modxtalks
 * @subpackage build
 */
$settings = array();
$settings['modxtalks.emailsFrom']= $modx->newObject('modSystemSetting');
$settings['modxtalks.emailsFrom']->fromArray(array(
    'key' => 'modxtalks.emailsFrom',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'emailNotifications',
),'',true,true);
$settings['modxtalks.emailsReplyTo']= $modx->newObject('modSystemSetting');
$settings['modxtalks.emailsReplyTo']->fromArray(array(
    'key' => 'modxtalks.emailsReplyTo',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'emailNotifications',
),'',true,true);
$settings['modxtalks.highlight']= $modx->newObject('modSystemSetting');
$settings['modxtalks.highlight']->fromArray(array(
    'key' => 'modxtalks.highlight',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'highlight',
),'',true,true);
$settings['modxtalks.highlighttheme']= $modx->newObject('modSystemSetting');
$settings['modxtalks.highlighttheme']->fromArray(array(
    'key' => 'modxtalks.highlighttheme',
    'value' => 'GitHub',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'highlight',
),'',true,true);
$settings['modxtalks.smileys']= $modx->newObject('modSystemSetting');
$settings['modxtalks.smileys']->fromArray(array(
    'key' => 'modxtalks.smileys',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settingBBCode',
),'',true,true);
$settings['modxtalks.editOptionsControls']= $modx->newObject('modSystemSetting');
$settings['modxtalks.editOptionsControls']->fromArray(array(
    'key' => 'modxtalks.editOptionsControls',
    'value' => 'fixed,image,link,strike,header,italic,bold,video,quote',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settingBBCode',
),'',true,true);
$settings['modxtalks.detectUrls']= $modx->newObject('modSystemSetting');
$settings['modxtalks.detectUrls']->fromArray(array(
    'key' => 'modxtalks.detectUrls',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settingBBCode',
),'',true,true);
$settings['modxtalks.bbcode']= $modx->newObject('modSystemSetting');
$settings['modxtalks.bbcode']->fromArray(array(
    'key' => 'modxtalks.bbcode',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settingBBCode',
),'',true,true);
$settings['modxtalks.scrubberTop']= $modx->newObject('modSystemSetting');
$settings['modxtalks.scrubberTop']->fromArray(array(
    'key' => 'modxtalks.scrubberTop',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.scrubberOffsetTop']= $modx->newObject('modSystemSetting');
$settings['modxtalks.scrubberOffsetTop']->fromArray(array(
    'key' => 'modxtalks.scrubberOffsetTop',
    'value' => 0,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.preModarateComments']= $modx->newObject('modSystemSetting');
$settings['modxtalks.preModarateComments']->fromArray(array(
    'key' => 'modxtalks.preModarateComments',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.onlyAuthUsers']= $modx->newObject('modSystemSetting');
$settings['modxtalks.onlyAuthUsers']->fromArray(array(
    'key' => 'modxtalks.onlyAuthUsers',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.mtgravatarSize']= $modx->newObject('modSystemSetting');
$settings['modxtalks.mtgravatarSize']->fromArray(array(
    'key' => 'modxtalks.mtgravatarSize',
    'value' => 64,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.mtGravator']= $modx->newObject('modSystemSetting');
$settings['modxtalks.mtGravator']->fromArray(array(
    'key' => 'modxtalks.mtGravator',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.mtDateFormat']= $modx->newObject('modSystemSetting');
$settings['modxtalks.mtDateFormat']->fromArray(array(
    'key' => 'modxtalks.mtDateFormat',
    'value' => 'j-m-Y, G:i',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.moderator']= $modx->newObject('modSystemSetting');
$settings['modxtalks.moderator']->fromArray(array(
    'key' => 'modxtalks.moderator',
    'value' => 'Administrator',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.jquery']= $modx->newObject('modSystemSetting');
$settings['modxtalks.jquery']->fromArray(array(
    'key' => 'modxtalks.jquery',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.jquery']= $modx->newObject('modSystemSetting');
$settings['modxtalks.jquery']->fromArray(array(
    'key' => 'modxtalks.jquery',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.edit_time']= $modx->newObject('modSystemSetting');
$settings['modxtalks.edit_time']->fromArray(array(
    'key' => 'modxtalks.edit_time',
    'value' => 180,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.defaultAvatar']= $modx->newObject('modSystemSetting');
$settings['modxtalks.defaultAvatar']->fromArray(array(
    'key' => 'modxtalks.defaultAvatar',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.commentsPerPage']= $modx->newObject('modSystemSetting');
$settings['modxtalks.commentsPerPage']->fromArray(array(
    'key' => 'modxtalks.commentsPerPage',
    'value' => 20,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.commentLength']= $modx->newObject('modSystemSetting');
$settings['modxtalks.commentLength']->fromArray(array(
    'key' => 'modxtalks.commentLength',
    'value' => 2000,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.ajax']= $modx->newObject('modSystemSetting');
$settings['modxtalks.ajax']->fromArray(array(
    'key' => 'modxtalks.ajax',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);
$settings['modxtalks.add_timeout']= $modx->newObject('modSystemSetting');
$settings['modxtalks.add_timeout']->fromArray(array(
    'key' => 'modxtalks.add_timeout',
    'value' => 60,
    'xtype' => 'textfield',
    'namespace' => 'modxtalks',
    'area' => 'settings',
),'',true,true);

return $settings;