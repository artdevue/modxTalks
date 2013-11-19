<?php
class getConversationsListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksConversation';
    public $defaultSortField = 'id';
    public $languageTopics = array('modxtalks:default');

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = trim($this->getProperty('query'));
        if ($query !== '') {
            $c->andCondition(array('conversation:LIKE' => "%{$query}%"));
        }
        return $c;
    }

    /**
     * Iterate across the data
     *
     * @param array $data
     * @return array
     */
    public function iterate(array $data) {
        $list = array();
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;
        /** @var xPDOObject|modAccessibleObject $object */
        foreach ($data['results'] as $object) {
            if ($this->checkListPermission && $object instanceof modAccessibleObject && !$object->checkPolicy('list')) continue;
            $properties = $object->getProperties('comments');
            $id = $object->getSingleProperty('id');
            $objectArray = $this->prepareRow($object);
            if (!empty($objectArray) && is_array($objectArray)) {
                unset($objectArray['properties']);
                $objectArray['link'] = 0;
                if ($this->modx->getCount('modResource', $id)) {
                    $objectArray['link'] = $this->modx->makeUrl($id);
                }
                $list[] = array_merge($objectArray, $properties);
                $this->currentIndex++;
            }
        }
        $list = $this->afterIteration($list);
        return $list;
    }

}

return 'getConversationsListProcessor';