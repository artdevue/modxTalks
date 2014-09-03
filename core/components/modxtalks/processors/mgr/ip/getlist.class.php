<?php

/**
 * Get list of blocked IP addresses
 *
 * @package modxtalks
 * @subpackage processors
 */
class getIpBlockListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'modxTalksIpBlock';
	public $defaultSortField = 'id';
	public $languageTopics = ['modxtalks:default'];

	/**
	 * Can be used to adjust the query prior to the COUNT statement
	 *
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{
		if ($query = $this->getProperty('query', null))
		{
			$c->where([
				'ip:LIKE' => '%' . $query . '%'
			]);
		}

		return $c;
	}

	/**
	 * @param xPDOObject|modxTalksIpBlock $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object)
	{
		$resourceArray = parent::prepareRow($object);

		if ( ! empty($resourceArray['date']))
		{
			$resourceArray['publishedon_date'] = date('j M Y', $resourceArray['date']);
			$resourceArray['publishedon_time'] = date('g:s A', $resourceArray['date']);
			$resourceArray['actions'] = [
				'text' => $this->modx->lexicon('delete'),
			];
		}

		return $resourceArray;
	}
}

return 'getIpBlockListProcessor';
