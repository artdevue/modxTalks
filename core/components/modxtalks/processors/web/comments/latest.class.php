<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Get Latest Comments
 *
 * @package modxtalks
 * @subpackage processors
 */
class getLatestCommentsListProcessor extends modObjectGetListProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksLatestPost';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.latestpost';
	public $limit;
	public $time;
	public $action;

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function process()
	{
		$this->action = $this->getProperty('action');
		$data = $this->getData();
		if ($this->action === 'latest')
		{
			$output = [];
			foreach ($data['results'] as $r)
			{
				$output[$r['cid']] = $this->app()->_parseTpl($this->app()->config['commentLatestTpl'], $r, true);
			}

			return $this->outputArray($output, $data['total']);
		}

		return $data;
	}

	public function getData()
	{
		$this->time = (int) $this->getProperty('time');
		$this->limit = (int) $this->app()->config['commentsLatestLimit'];

		$data = ['total' => 0, 'results' => []];

		$q = $this->modx->newQuery('modxTalksLatestPost');
		if ($this->action === 'latest')
		{
			$q->where(['time:>' => $this->time]);
		}

		$count = $this->modx->getCount('modxTalksLatestPost', $q);

		if ($count == 0)
		{
			return $data;
		}

		$q->select($this->modx->getSelectColumns('modxTalksLatestPost'));
		$q->sortby('time', 'DESC');
		$q->limit($this->limit);

		$comments = [];
		if ($q->prepare() && $q->stmt->execute())
		{
			$comments = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		$list = [];
		$date_format = $this->app()->config['dateFormat'];

		foreach ($comments as $k => $comment)
		{
			/**
			 * Prepare data for published comment
			 */
			$comment['content'] = $this->modx->stripTags($comment['content']);
			$list[] = [
				'name' => $comment['name'],
				'avatar' => $this->app()->getAvatar($comment['email']),
				'date' => date($date_format . ' O', $comment['time']),
				'funny_date' => $this->app()->date_format($comment['time']),
				'id' => $comment['pid'],
				'cid' => $comment['cid'],
				'idx' => $comment['idx'],
				'link' => $comment['link'],
				'timeago' => date('c', $comment['time']),
				'time' => $comment['time'],
				'content' => $this->app()->slice($comment['content']),
				'total' => $comment['total'],
				'title' => $comment['title'],
			];
		}

		$data['total'] = $count;
		$data['results'] =& $list;

		return $data;
	}
}

return 'getLatestCommentsListProcessor';
