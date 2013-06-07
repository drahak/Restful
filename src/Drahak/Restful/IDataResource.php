<?php
namespace Drahak\Restful;

/**
 * IDataResource
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IDataResource
{

	/**
	 * Get result set data
	 * @return array|\stdClass|\Traversable
	 */
	public function getData();

}