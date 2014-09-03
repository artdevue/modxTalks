<?php
/**
 * @package modxtalks
 */
if ( ! isset($modx->modxtalks) || ! $modx->modxtalks instanceof modxTalks)
{
	$modx->modxtalks = $modx->getService('modxtalks', 'modxTalks', $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/') . 'model/modxtalks/', $scriptProperties);
}

$comments = $modx->modxtalks->getLatestComments();

return $comments;
