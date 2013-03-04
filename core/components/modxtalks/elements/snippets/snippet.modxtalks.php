<?php
/**
 * @package modxtalks
 */
#$time = microtime(true);
#$startMemory = memory_get_usage()/pow(1024,2);
#print_r(sprintf('Memory Before: %2.2f Mbytes',$startMemory));

if (!isset($modx->modxtalks) || !($modx->modxtalks instanceof modxTalks)) {
    $modx->modxtalks = $modx->getService('modxtalks','modxTalks',$modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/').'model/modxtalks/',$scriptProperties);
}

$comments = $modx->modxtalks->init();

#$comments .= sprintf('Time: %2.2f мс',(microtime(true) - $time)*1000);
#$comments .= sprintf('Memory After: %2.2f Mbytes',memory_get_usage()/pow(1024,2));

return $comments;