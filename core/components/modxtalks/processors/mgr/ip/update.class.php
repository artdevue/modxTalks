<?php

/**
 * Update blocked IP address
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksIpBlockUpdateProcessor extends modObjectUpdateProcessor
{
	public $classKey = 'modxTalksIpBlock';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.ip';

	public function initialize()
	{
		$data = $this->getProperty('data');
		if ($data = $this->modx->fromJSON($data))
		{
			$this->properties = $data;
		}

		return parent::initialize();
	}

	public function beforeSave()
	{
		$this->properties = [
			'ip' => $this->getProperty('ip'),
			'intro' => $this->getProperty('intro'),
		];

		return parent::beforeSave();
	}
}

return 'modxTalksIpBlockUpdateProcessor';
