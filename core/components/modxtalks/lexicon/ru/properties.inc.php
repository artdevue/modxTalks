<?php
/**
 * MODXTalks
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 *
 */
/**
 * MODXTalks properties Russian language file
 *
 * @package modxtalks
 * @subpackage lexicon
 */

/* options */
$_lang['area'] = 'Раздел';
$_lang['area_all'] = 'Общие';
$_lang['area_settings'] = 'Общие настройки';
$_lang['area_temlates'] = 'Шаблоны';
$_lang['area_comment'] = 'Комментарий';

/* MODXTalks */
$_lang['modxtalks.prop_conversation_desc'] = 'Уникальное название для блока вывода комментариев, по-умолчанию <code><span style="color: #009900;">[</span><span style="color: #009900;">[</span><span style="color: #339933;">*</span>class_key<span style="color: #009900;">]</span><span style="color: #009900;">]</span><span style="color: #339933;">-</span><span style="color: #009900;">[</span><span style="color: #009900;">[</span><span style="color: #339933;">*</span>id<span style="color: #009900;">]</span><span style="color: #009900;">]</span></code>';
$_lang['modxtalks.prop_moderator_desc'] = 'Укажите через запятую группы модераторов, которые могут модерировать комментарии';
$_lang['modxtalks.prop_onlyAuthUsers_desc'] = 'Оставлять комментарии могут только авторизованные пользователи.';
$_lang['modxtalks.prop_gravatar_desc'] = 'Включить Граватар в сообщениях пользователей. Да / Нет';
$_lang['modxtalks.prop_gravatarSize_desc'] = 'Размер выводимого в комментариях аватара с помощью Граватар сервиса.';
$_lang['modxtalks.prop_defaultAvatar_desc'] = 'Полная ссылка на аватар, которая отображается при отсутствии аватара через сервис Граватар.';
$_lang['modxtalks.prop_dateFormat_desc'] = 'Формат отображаемой в комментариях даты.';
$_lang['modxtalks.prop_commentsPerPage_desc'] = 'Максимальное количество комментариев на странице для отображения при визуализации управления навигации по страницам.';
$_lang['modxtalks.prop_add_timeout_desc'] = 'Время в секундах, между отправкой комментариев.';
$_lang['modxtalks.prop_edit_time_desc'] = 'Время, после которого пользователь не может редактировать комментарий';
$_lang['modxtalks.prop_commentsClosed_desc'] = 'Запретить оставлять комментарии всем, кроме выбранных групп модераторов. Комментирование отключено.';
$_lang['modxtalks.prop_commentLength_desc'] = 'Максимально допустимая длина комментария в символах.';
$_lang['modxtalks.prop_commentTpl_desc'] = 'Шаблон для вывода комментариев.';
$_lang['modxtalks.prop_deletedCommentTpl_desc'] = 'Шаблон для вывода удалённых комментариев.';
$_lang['modxtalks.prop_commentAddFormTpl_desc'] = 'Шаблон формы добавления комментария.';
$_lang['modxtalks.prop_commentEditFormTpl_desc'] = 'Шаблон формы редактирования комментария.';
$_lang['modxtalks.prop_commentAuthTpl_desc'] = 'Шаблон формы регистрации. Выводится неавторизированным юзерам, если параметр onlyAuthUsers активный.';

/* modxTalksLatestComments */
$_lang['modxtalks.prop_commentLatestTpl_desc'] = 'Шаблон отдельного комментария для блока. Может быть название чанка. Если установленно значение, то заменит исходный шаблон.';
$_lang['modxtalks.prop_commentsLatestOutTpl_desc'] = 'Шаблон оболочки комментариев для блока. Может быть название чанка. Если установленно значение, то заменит исходный шаблон.';
$_lang['modxtalks.prop_commentsLatestLimit_desc'] = 'Ограничения вывода количество комментариев на страницу.';
