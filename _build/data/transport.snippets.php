<?php
/**
 * @var modX $modx
 * @var array $sources
 * @package articles
 * @subpackage build
 */
$snippets = array();

$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'modxTalks',
    'description' => '[modxTalks] Snippet.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalks.php'),
));
$properties = include $sources['properties'].'properties.modxtalks.php';
$snippets[0]->setProperties($properties);
unset($properties);

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'modxTalksAjax',
    'description' => '[modxTalksAjax] Snippet.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalksajax.php'),
));

$snippets[2]= $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => 2,
    'name' => 'modxTalksLatestComments',
    'description' => 'The conclusion of the latest comments on your site.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalkslatestcomments.php'),
));
$properties = include $sources['properties'].'properties.modxtalkslatestcomments.php';
$snippets[2]->setProperties($properties);
unset($properties);

$snippets[3]= $modx->newObject('modSnippet');
$snippets[3]->fromArray(array(
    'id' => 3,
    'name' => 'MtCount',
    'description' => 'Snippet to run the page parser and display the number of comments on the resource.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.mtcount.php'),
));

return $snippets;