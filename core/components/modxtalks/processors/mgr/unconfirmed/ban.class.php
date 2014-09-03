<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Ban IP address from unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostBanProcessor extends modObjectRemoveProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksTempPost';
	public $objectType = 'modxtalks.temppost';
	public $languageTopics = ['modxtalks:default'];
	public $beforeRemoveEvent = '';
	public $afterRemoveEvent = '';

	public function beforeRemove()
	{
		$conversation = $this->modx->getObject('modxTalksConversation', $this->object->conversationId);

		if ( ! $conversation)
		{
			return $this->app()->lang('empty_conversation');
		}

		if ($conversation->unconfirmed)
		{
			$conversation->unconfirmed -= 1;
		}

		if ($conversation->save() !== true)
		{
			return $this->app()->lang('error');
		}

		if ($this->modx->getCount('modxTalksIpBlock', ['ip' => $this->object->ip]))
		{
			return $this->app()->lang('ip_blocked');
		}

		$ip = $this->modx->newObject('modxTalksIpBlock', [
			'ip' => $this->object->ip,
			'date' => time(),
			'intro' => '',
		]);

		if ( ! $ip->save())
		{
			return $this->app()->lang('ip_save_error');
		}

		if ($this->object->userId > 0)
		{
			if ($user = $this->modx->getObject('modUser', $this->object->userId))
			{
				$user->set('active', false);
				if ( ! $user->save())
				{
					return $this->app()->lang('error');
				}
			}
		}

		return parent::beforeRemove();
	}

	public function afterRemove()
	{
		$this->success($this->app()->lang('ip_ban_success'));

		return parent::afterRemove();
	}
}

return 'modxTalksTempPostBanProcessor';
