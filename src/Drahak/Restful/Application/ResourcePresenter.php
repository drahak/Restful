<?php
namespace Drahak\Restful\Application;

use Drahak\Restful\IInput;
use Drahak\Restful\IResourceFactory;
use Drahak\Restful\IResponseFactory;
use Drahak\Restful\InvalidStateException;
use Drahak\Restful\IResource;
use Drahak\Restful\Resource;
use Drahak\Restful\Security\AuthenticationContext;
use Drahak\Restful\Security\RequestAuthenticator;
use Drahak\Restful\Security\SecurityException;
use Nette\Utils\Strings;
use Nette\Application;
use Nette\Application\UI;
use Nette\Application\IResponse;
use Nette\Http;

/**
 * Base presenter for REST API presenters
 * @package Drahak\Restful\Application
 * @author Drahomír Hanák
 */
abstract class ResourcePresenter extends UI\Presenter implements IResourcePresenter
{

	/** @var array */
	protected $formats = array(
		'json' => IResource::JSON,
		'xml' => IResource::XML,
		'jsonp' => IResource::JSONP,
		'query' => IResource::QUERY,
		'data_url' => IResource::DATA_URL
	);

	/** @var string */
	protected $defaultContentType = IResource::JSON;

	/** @var IResource */
	protected $resource;

	/** @var IInput */
	protected $input;

	/** @var IResourceFactory */
	protected $resourceFactory;

	/** @var IResponseFactory */
	protected $responseFactory;

	/** @var AuthenticationContext */
	protected $authentication;

	/**
	 * Inject response factory
	 * @param IResponseFactory $responseFactory
	 */
	public function injectResponseFactory(IResponseFactory $responseFactory)
	{
		$this->responseFactory = $responseFactory;
	}

	/**
	 * Inject resource factory
	 * @param IResourceFactory $resourceFactory
	 */
	public function injectResourceFactory(IResourceFactory $resourceFactory)
	{
		$this->resourceFactory = $resourceFactory;
	}

	/**
	 * Inject authentication strategy context
	 * @param AuthenticationContext $authentication
	 */
	public function injectRequestAuthenticator(AuthenticationContext $authentication)
	{
		$this->authentication = $authentication;
	}

	/**
	 * Inject input
	 * @param IInput $input
	 */
	public function injectInput(IInput $input)
	{
		$this->input = $input;
	}

	/**
	 * Presenter startup
	 */
	protected function startup()
	{
		parent::startup();
		$this->resource = $this->resourceFactory->create();
		if ($this->defaultContentType) {
			$this->resource->setContentType($this->defaultContentType);
		}

		$accept = explode(',', $this->getHttpRequest()->getHeader('Accept'));
		foreach ($accept as $mimeType) {
			foreach ($this->formats as $formatMime) {
				if (Strings::contains($mimeType, $formatMime)) {
					$this->resource->setContentType($formatMime);
					break;
				}
			}
		}
	}

	/**
	 * Check security requirements
	 * @param $element
	 */
	public function checkRequirements($element)
	{
		try {
			parent::checkRequirements($element);
		} catch (Application\ForbiddenRequestException $e) {
			$this->sendErrorResource($e);
		}

		try {
			$this->authentication->authenticate($this->input);
		} catch (SecurityException $e) {
			$this->sendErrorResource($e);
		}
	}

	/**
	 * On before render
	 */
	protected function beforeRender()
	{
		parent::beforeRender();
		$this->sendResource();
	}

	/**
	 * Get REST API response
	 * @param string $contentType
	 * @param int $code
	 * @return IResponse
	 *
	 * @throws InvalidStateException
	 */
	public function sendResource($contentType = NULL, $code = 200)
	{
		if ($contentType !== NULL) {
			$this->resource->setContentType($contentType);
		}

		$this->getHttpResponse()->setCode($code);
		$response = $this->responseFactory->create($this->resource, $code);
		$this->sendResponse($response);
	}

	/**
	 * Send error resource to output
	 * @param \Exception $e
	 */
	protected function sendErrorResource(\Exception $e)
	{
		$code = $e->getCode() ? $e->getCode() : 500;

		$this->resource = $this->resourceFactory->create();
		$this->resource->code = $code;
		$this->resource->status = 'error';
		$this->resource->message = $e->getMessage();

		$this->sendResource($this->defaultContentType, $code);
	}

}