<?php
/**
 * @package modxtalks
 */
$scriptProperties['conversation'] = $modx->getOption('conversation',$scriptProperties,$modx->resource->class_key.'-'.$modx->resource->id);
$modx->lexicon->load('modxtalks:default');
$modx->modxtalks = $modx->getService('modxtalks','modxTalks',$modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/').'model/modxtalks/',$scriptProperties);
if (!($modx->modxtalks instanceof modxTalks)) return '';

$comments = $modx->modxtalks->init();

return $comments;