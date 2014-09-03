<?php

/**
 * Get a list of Conversations
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalkCommentsGetListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'modxTalksConversation';
	public $languageTopics = ['modxtalks:default'];

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function process()
	{
		$cid = $this->getProperty('conversationName');
		$data = [
			'total' => 0,
			'deleted' => 0,
			'unconfirmed' => 0,
		];

		$conversation = $this->modx->getObject($this->classKey, [
			'conversation' => $cid
		]);

		if ($conversation)
		{
			$data['total'] = $conversation->total;
			$data['deleted'] = $conversation->deleted;
			$data['unconfirmed'] = $conversation->unconfirmed;
		}

		return $this->outputArray($data);
	}

	/**
	 * Return arrays of comments counts converted to JSON.
	 *
	 * @access public
	 *
	 * @param array $data An array of data objects.
	 * @param mixed $count For backwards compatibility, do not use this key
	 *
	 * @return string The JSON output.
	 */
	public function outputArray(array $data, $count = false)
	{
		return json_encode($data);
	}
}

return 'modxTalkCommentsGetListProcessor';
