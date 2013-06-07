<?php
namespace Tests\Drahak\Restful\Application\Responses;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Responses\JsonpResponse;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Responses\JsonpResponse.
 *
 * @testCase Tests\Drahak\Restful\Application\Responses\JsonpResponseTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Responses
 */
class JsonpResponseTest extends TestCase
{

	/** @var MockInterface */
	private $httpRequest;

	/** @var MockInterface */
	private $httpResponse;

	/** @var JsonpResponse */
	private $response;
    
    protected function setUp()
    {
		parent::setUp();
		$this->httpRequest = $this->mockista->create('Drahak\Restful\Http\IRequest');
		$this->httpResponse = $this->mockista->create('Nette\Http\IResponse');
		$this->response = new JsonpResponse(array('test' => 'JSONP'));
    }
    
    public function testResponseWithJSONP()
    {
		$headers = array('X-Testing' => true);
		$this->httpResponse->expects('setContentType')
			->once()
			->with('application/javascript');
		$this->httpResponse->expects('getCode')
			->once()
			->andReturn(200);
		$this->httpResponse->expects('getHeaders')
			->once()
			->andReturn($headers);
		$this->httpRequest->expects('getJsonp')
			->once()
			->andReturn('callbackFn');

		ob_start();
		$this->response->send($this->httpRequest, $this->httpResponse);
		$content = ob_get_contents();
		ob_end_flush();

		Assert::same($content, 'callbackFn({"response":{"test":"JSONP"},"status_code":200,"headers":{"X-Testing":true}});');
    }


	public function testWebalizeCallbackFunctionNameAndKeepUpperCase()
	{
		$headers = array('X-Testing' => true);
		$this->httpResponse->expects('setContentType')
			->once()
			->with('application/javascript');
		$this->httpResponse->expects('getCode')
			->once()
			->andReturn(200);
		$this->httpResponse->expects('getHeaders')
			->once()
			->andReturn($headers);
		$this->httpRequest->expects('getJsonp')
			->once()
			->andReturn('ěščřžýáíéAnd+_-! ?');

		ob_start();
		$this->response->send($this->httpRequest, $this->httpResponse);
		$content = ob_get_contents();
		ob_end_flush();

		Assert::same($content, 'escrzyaieAnd({"response":{"test":"JSONP"},"status_code":200,"headers":{"X-Testing":true}});');
	}

	public function testThrowsExceptionWhenInvalidRequestIsGiven()
	{
		$request = $this->mockista->create('Nette\Http\IRequest');
		Assert::throws(function() use($request) {
			$this->response->send($request, $this->httpResponse);
		}, 'Drahak\Restful\InvalidArgumentException');
	}

}
\run(new JsonpResponseTest());