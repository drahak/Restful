<?php
namespace Drahak\Restful\Mapping;

use Drahak\Restful\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * JsonMapper
 * @package Drahak\Restful\Mapping
 * @author Drahomír Hanák
 */
class JsonMapper extends Object implements IMapper
{

	/**
	 * Convert array or Traversable input to string output response
	 * @param array|\Traversable $data
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 * @throws MappingException
	 */
	public function parseResponse($data)
	{
		if (!is_array($data) && $data instanceof \Traversable && $data !== NULL) {
			throw new InvalidArgumentException('Data must be of type array, traversable or null');
		}

		try {
			return Json::encode($data);
		} catch(JsonException $e) {
			throw new MappingException('Error in parsing response: ' .$e->getMessage());
		}
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
		try {
			return (array)Json::decode($data);
		} catch (JsonException $e) {
			throw new MappingException('Error in parsing request: ' . $e->getMessage());
		}
	}


}