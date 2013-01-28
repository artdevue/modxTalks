<?php
/**
 * @var modX $modx
 * @var array $sources
 * @package articles
 * @subpackage build
 */
$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'modxTalks',
    'description' => '[modxTalks] Snippet.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalks.php'),
));
return $snippets;