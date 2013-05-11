<?php
namespace Drahak\Restful\Mapping;

use Nette\Object;

/**
 * Query string mapper
 * @package Drahak\Restful\Mapping
 * @author Drahomír Hanák
 */
class QueryMapper extends Object implements IMapper
{
	/**
	 * Convert array or Traversable input to string output response
	 * @param array $data
	 * @return mixed
	 *
	 * @throws MappingException
	 */
	public function parseResponse($data)
	{
		return http_build_query($data);
	}

	/**
	 * Convert client request data to array or traversable
	 * @param string $data
	 * @return array|\Traversable
	 *
	 * @throws MappingException
	 */
	public function parseRequest($data)
	{
		$result = array();
		parse_str($data, $result);
		return $result;
	}


}