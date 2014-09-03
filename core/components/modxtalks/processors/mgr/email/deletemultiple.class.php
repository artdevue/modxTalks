<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Remove selected Email addresses from Block List
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksEmailBlockMultipleProcessor extends modObjectProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksEmailBlock';
	public $languageTopics = ['modxtalks:default'];

	public function process()
	{
		if ( ! $ids = $this->getProperty('ids', null))
		{
			return $this->failure($this->app()->lang('post_err_ns_multiple'));
		}

		$ids = is_array($ids) ? $ids : explode(',', $ids);

		$addresses = $this->modx->removeCollection($this->classKey, [
			'id:IN' => $ids
		]);

		if ( ! $addresses)
		{
			return $this->failure($this->app()->lang('post_err_ns_multiple'));
		}

		return $this->success();
	}
}

return 'modxTalksEmailBlockMultipleProcessor';
