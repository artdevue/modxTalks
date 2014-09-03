<?php

trait modxTalksProcessorTrait
{
	/**
	 * Get modxTalks App
	 *
	 * @return modxTalks
	 */
	protected function app()
	{
		return $this->modx->modxtalks;
	}
}
