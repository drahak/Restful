<?php
namespace Drahak\Restful\Application\Responses;

use Drahak;
use Drahak\Restful\InvalidArgumentException;
use Drahak\Restful\Mapping\JsonMapper;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Strings;

/**
 * JSONP response
 * @package Drahak\Restful\Application\Responses
 * @author Drahomír Hanák
 */
class JsonpResponse extends BaseResponse
{

	/** @var array|\stdClass|\Traversable */
	private $data;

	public function __construct($data, $contentType = NULL)
	{
		parent::__construct($contentType);
		$this->data = $data;
		$this->mapper = new JsonMapper;
	}

	/**
	 * Send JSONP response to output
	 * @param IRequest $httpRequest
	 * @param IResponse $httpResponse
	 * @throws \Drahak\Restful\InvalidArgumentException
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse)
	{
		if (!$httpRequest instanceof Drahak\Restful\Http\IRequest) {
			throw new InvalidArgumentException(
				'JsonpResponse expects Drahak\Restful\Http\IRequest as a first parameter, ' . get_class($httpRequest) . ' given'
			);
		}

		$httpResponse->setContentType($this->contentType ? $this->contentType : 'application/javascript');

		$data = array();
		$data['response'] = $this->data;
		$data['status_code'] = $httpResponse->getCode();
		$data['headers'] = $httpResponse->getHeaders();

		$callback = $httpRequest->getJsonp() ? Strings::webalize($httpRequest->getJsonp(), NULL, FALSE) : '';
		echo $callback . '(' . $this->mapper->parseResponse($data) . ');';
	}


}