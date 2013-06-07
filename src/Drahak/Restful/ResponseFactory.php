<?php
namespace Drahak\Restful;

use Drahak\Restful\IResource;
use Drahak\Restful\Http\IRequest;
use Drahak\Restful\Resource\EnvelopeDecorator;
use Nette\Http\IResponse;
use Nette\Object;

/**
 * REST ResponseFactory
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
class ResponseFactory extends Object implements IResponseFactory
{

	/** @var IResponse */
	private $response;

	/** @var IRequest */
	private $request;

	/** @var array */
	private $responses = array(
		IResource::JSON => 'Nette\Application\Responses\JsonResponse',
		IResource::JSONP => 'Drahak\Restful\Application\Responses\JsonpResponse',
		IResource::QUERY => 'Drahak\Restful\Application\Responses\QueryResponse',
		IResource::XML => 'Drahak\Restful\Application\Responses\XmlResponse',
		IResource::DATA_URL => 'Drahak\Restful\Application\Responses\DataUrlResponse',
		IResource::NULL => 'Drahak\Restful\Application\Responses\NullResponse'
	);

	public function __construct(IResponse $response, IRequest $request)
	{
		$this->response = $response;
		$this->request = $request;
	}

	/**
	 * Register new response type to factory
	 * @param string $mimeType
	 * @param string $responseClass
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerResponse($mimeType, $responseClass)
	{
		if (!class_exists($responseClass)) {
			throw new InvalidArgumentException('Response class does not exist.');
		}

		$this->responses[$mimeType] = $responseClass;
		return $this;
	}

	/**
	 * Unregister API response fro mfactory
	 * @param string $mimeType
	 */
	public function unregisterResponse($mimeType)
	{
		unset($this->responses[$mimeType]);
	}

	/**
	 * Create new API response
	 * @param IResource $resource
	 * @return IResponse
	 * @throws InvalidStateException
	 */
	public function create(IResource $resource)
	{
		$contentType = $resource->getContentType();
		if (!isset($this->responses[$contentType])) {
			throw new InvalidStateException('Unregistered API response.');
		}

		if (!class_exists($this->responses[$contentType])) {
			throw new InvalidStateException('API response class does not exist.');
		}

		if ($this->request->isJsonp()) {
			$contentType = IResource::JSONP;
		}

		$responseClass = $this->responses[$contentType];
		$response = new $responseClass($resource->getData());
		return $response;
	}

}