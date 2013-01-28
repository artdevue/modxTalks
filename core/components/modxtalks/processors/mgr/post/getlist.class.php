<?php
/**
 * Get a list of modxTalks
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalkGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'Post';
    public $languageTopics = array('modxtalks:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'title:LIKE' => '%'.$query.'%',
                'OR:conversationId:LIKE' => '%'.$query.'%',
            ));
        }
        return $c;
    }
}
return 'modxTalkGetListProcessor';