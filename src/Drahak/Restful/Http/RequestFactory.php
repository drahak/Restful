<?php
namespace Drahak\Restful\Http;

use Nette;

/**
 * RequestFactory
 * @package Drahak\Restful\Http
 * @author Drahomír Hanák
 */
class RequestFactory extends Nette\Http\RequestFactory
{

	/** @var string|null|bool */
	private $jsonpKey;

	public function __construct($jsonpKey)
	{
		$this->jsonpKey = $jsonpKey;
	}

	/**
	 * Create HTTP request
	 * @return Request
	 */
	public function createHttpRequest()
	{
		$netteRequest = parent::createHttpRequest();
		$request = new Request(
			$netteRequest->url, $netteRequest->query, $netteRequest->post, $netteRequest->files, $netteRequest->cookies, $netteRequest->headers,
			$netteRequest->method, $netteRequest->remoteAddress, $netteRequest->remoteHost
		);
		$request->setJsonpKey($this->jsonpKey);
		return $request;
	}

}