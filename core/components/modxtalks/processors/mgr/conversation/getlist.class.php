<?php

/**
 * Get Conversations
 *
 * @package modxtalks
 * @subpackage processors
 */
class getConversationsListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksConversation';
    public $defaultSortField = 'id';
    public $languageTopics = array('modxtalks:default');
    public $parentTitleField;
    public $parentClassKey;

    public function beforeQuery() {
        $this->parentTitleField = $this->modx->getOption('modxtalks.parent_table_title_field', null, 'pagetitle');
        $this->parentClassKey = $this->modx->getOption('modxtalks.parent_table_class_key', null, 'modDocument');

        return parent::beforeQuery();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->leftJoin($this->parentClassKey, 'parent', 'parent.id = modxTalksConversation.rid');
        $c->select(['parent.id', 'parent.' . $this->parentTitleField . ' as title']);

        $query = trim($this->getProperty('query'));
        if ($query !== '') {
            $c->andCondition(array(
                'conversation:LIKE' => "%{$query}%",
                'OR:parent.' . $this->parentTitleField . ':LIKE' => "%{$query}%"
            ));
        }

        return $c;
    }

    /**
     * Iterate across the data
     *
     * @param array $data
     *
     * @return array
     */
    public function iterate(array $data) {
        $list = array();
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;

        /** @var xPDOObject|modAccessibleObject $object */
        foreach ($data['results'] as $object) {
            if ($this->checkListPermission && $object instanceof modAccessibleObject && !$object->checkPolicy('list')) {
                continue;
            }

            $properties = $object->getProperties('comments');
            $objectArray = $this->prepareRow($object);

            if (!empty($objectArray) && is_array($objectArray)) {
                unset($objectArray['properties']);

                $objectArray['link'] = 0;
                if ($objectArray['title'] != '') {
                    $objectArray['link'] = $this->modx->makeUrl($object->rid, '', '', $scheme = 'full');
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
