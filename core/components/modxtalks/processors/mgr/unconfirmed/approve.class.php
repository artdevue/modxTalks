<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Approve unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostApproveProcessor extends modObjectRemoveProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksTempPost';
	public $objectType = 'modxtalks.temppost';
	public $languageTopics = ['modxtalks:default'];
	/**
	 * @var modxTalkCreateProcessor
	 */
	protected $conversation;

	public function beforeRemove()
	{
		$this->conversation = $this->modx->getObject('modxTalksConversation', $this->object->conversationId);

		if ( ! $this->conversation)
		{
			return $this->app()->lang('empty_conversation');
		}

		$this->conversation->total += 1;
		if ($this->conversation->unconfirmed)
		{
			$this->conversation->unconfirmed -= 1;
		}

		$q = $this->modx->newQuery('modxTalksPost', [
			'conversationId' => $this->conversation->id
		]);
		$q->sortby('idx', 'DESC');

		$idx = 0;

		if ($lastComment = $this->modx->getObject('modxTalksPost', $q))
		{
			$idx = $lastComment->get('idx');
		}

		$this->comment = $this->modx->newObject('modxTalksPost');
		$time = time();
		$commentParams = [
			'idx' => ++$idx,
			'conversationId' => $this->conversation->id,
			'time' => $time,
			'date' => strftime('%Y%m', $time),
			'content' => $this->object->content,
			'ip' => $this->object->ip,
		];

		if ($this->object->userId > 0)
		{
			$commentParams['userId'] = $this->object->userId;
		}
		else
		{
			$commentParams['username'] = $this->object->username;
			$commentParams['useremail'] = $this->object->useremail;
		}

		$this->comment->fromArray($commentParams);

		if ($this->comment->save() !== true)
		{
			return $this->app()->lang('error');
		}

		if ($this->conversation->save() !== true)
		{
			return $this->app()->lang('error');
		}

		return parent::beforeRemove();
	}

	public function afterRemove()
	{
		/**
		 * Обновляем кэш комментария и темы
		 */
		if ($this->app()->mtCache === true)
		{
			if ( ! $this->app()->cacheComment($this->comment))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/restore] Error cache the comment with ID ' . $this->comment->id);
			}

			if ( ! $this->app()->cacheConversation($this->conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/restore] Error cache conversation with ID ' . $this->conversation->id);
			}
		}

		/**
		 * Отправляем уведомление о подтверждении комментария пользователю оставившему комментарий
		 */
		if ( ! $this->app()->notifyUser($this->comment))
		{
			return $this->app()->lang('error');
		}

		$this->success($this->app()->lang('comment_approved'));

		return parent::afterRemove();
	}
}

return 'modxTalksTempPostApproveProcessor';
