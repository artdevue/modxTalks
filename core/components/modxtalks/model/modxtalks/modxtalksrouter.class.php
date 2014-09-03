<?php

/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author    Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package   modxtalks
 *
 */
class modxTalksRouter
{
	/** @var modX $modx */
	public $modx;
	/** @var array $config */
	public $config = [];
	/** @var array $config */
	public $aliasMap;

	function __construct(modX &$modx, array $config = [])
	{
		$this->modx =& $modx;
		$this->aliasMap =& $this->modx->aliasMap;
		$this->config = array_merge([
			'some_option' => true
		], $config);
	}

	/**
	 * Create conversations map
	 *
	 * @return array|false False if cache is off
	 */
	public function conversationsMap()
	{
		/**
		 * If disabled caching return False
		 */
		if ( ! $this->modx->getCacheManager())
		{
			return false;
		}
		/**
		 * If a map is present in the cache, then just return it
		 */
		$map = $this->modx->cacheManager->get('conversations_map', [xPDO::OPT_CACHE_KEY => 'modxtalks']);
		if ($map)
		{
			return $map;
		}
		/**
		 * If the map is not in the cache, we all topics from the database and build the map
		 */
		$map = [];
		$c = $this->modx->newQuery('modxTalksConversation');
		$c->select([
			'id',
			'rid',
			'conversation'
		]);
		if ($c->prepare() && $c->stmt->execute())
		{
			$conversations = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($conversations as $c)
			{
				$map[$c['rid']][$c['id']] = $c['conversation'];
			}
			$this->modx->cacheManager->set('conversations_map', $map, 0, [
				xPDO::OPT_CACHE_KEY => 'modxtalks'
			]);

		}

		return $map;
	}

	/**
	 * Check resource have conversations
	 *
	 * @param integer $id Resource ID
	 *
	 * @access private
	 * @return boolean True if resource have a conversation
	 */
	private function _hasConversations($id)
	{
		if ( ! intval($id))
		{
			return false;
		}
		/**
		 * Checking through the map so the cache
		 */
		if ($map = $this->conversationsMap())
		{
			/**
			 * If the resource contains at least one topic return true
			 */
			if (array_key_exists($id, $map))
			{
				return true;
			}

			return false;
		}
		elseif ($this->modx->getCount('modxTalksConversation', ['rid' => $id]))
		{
			/**
			 * If the conversationsMap () returned false (with the cache enabled), on-base
			 */
			return true;
		}

		return false;
	}

	/**
	 * Route the URL request based on the container IDs
	 *
	 * @return boolean
	 */
	public function route()
	{
		$serverUri = $this->modx->request->parameters['REQUEST'];
		$qmt = $serverUri[$this->modx->getOption('request_param_alias')];

		// check to see there is a link rss comments
		if (preg_match("@comments-(.*?).rss@si", $qmt, $data))
		{
			if (in_array($data[1], $this->aliasMap) && $data[0] == $qmt && $this->_hasConversations($data[1]))
			{
				$_REQUEST['comment'] = $data[1];
				$_REQUEST['rss'] = 'true';
				$this->modx->sendForward($data[1]);
			}
		}

		// check to see there is a link to a comment
		if ( ! preg_match("@/?comment-(.*?)-mt@si", $qmt, $data))
		{
			return true;
		}

		$alias = str_replace($data[0], '', $qmt);
		$id = $alias === '/' ? $this->modx->getOption('site_start', 1) : $this->aliasMap[$alias];
		if ($id)
		{
			if (isset($data[1]) && $this->_hasConversations($id))
			{
				if ($data[1] == 'last' || intval($data[1]) || date('Y-m', strtotime($data[1])))
				{
					$_REQUEST['comment'] = $data[1];
					$this->modx->sendForward($id);
				}
			}
		}

		return true;
	}
}
