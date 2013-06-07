<?php
namespace Drahak\Restful\Http;

use Nette;

/**
 * HTTP Request
 * @package Drahak\Restful\Http
 * @author Drahomír Hanák
 *
 * @property-write string $jsonpKey
 */
class Request extends Nette\Http\Request implements IRequest
{

	/** Request method header override name */
	const METHOD_OVERRIDE_HEADER = 'X-HTTP-Method-Override';

	/** Request method override query parameter name */
	const METHOD_OVERRIDE_PARAM = '__method';

	/** @var string */
	private $jsonpKey = 'jsonp';

	/**
	 * Set JSONP parameter name in query string
	 * @param string $name
	 */
	public function setJsonpKey($name)
	{
		$this->jsonpKey = $name;
	}

	/**
	 * Get request method
	 * @return string
	 */
	public function getMethod()
	{
		$method = parent::getMethod();
		if ($method !== self::POST) {
			return $method;
		}

		// Override request method with query param
		if ($this->getQuery(self::METHOD_OVERRIDE_PARAM)) {
			return $this->getQuery(self::METHOD_OVERRIDE_PARAM);
		}

		// Override request method with header
		if ($this->getHeader(self::METHOD_OVERRIDE_HEADER)) {
			return $this->getHeader(self::METHOD_OVERRIDE_HEADER);
		}
		return $method;
	}

	/**
	 * Get original method
	 * @return string
	 */
	public function getOriginalMethod()
	{
		return parent::getMethod();
	}

	/**
	 * Is JSONP request
	 * @return bool
	 */
	public function isJsonp()
	{
		return (bool)$this->getJsonp();
	}

	/**
	 * Get JSONP value - callback function name
	 * @return string|NULL
	 */
	public function getJsonp()
	{
		return $this->getQuery($this->jsonpKey);
	}

}