<?php

/**
 * Get list of blocked Email addresses
 *
 * @package modxtalks
 * @subpackage processors
 */
class getEmailBlockListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'modxTalksEmailBlock';
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
				'email:LIKE' => "%{$query}%"
			]);
		}

		return $c;
	}

	/**
	 * @param xPDOObject|modxTalksEmailBlock $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object)
	{
		$email = parent::prepareRow($object);

		if ( ! $email['intro'])
		{
			$email['intro'] = '';
		}
		if ( ! empty($email['date']))
		{
			$email['publishedon_date'] = date('j M Y', $email['date']);
			$email['publishedon_time'] = date('g:s A', $email['date']);
			$email['actions'] = [
				'text' => $this->modx->lexicon('delete'),
			];
		}

		return $email;
	}
}

return 'getEmailBlockListProcessor';
