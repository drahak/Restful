<?php
namespace Tests\Drahak\Restful\Http;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Http\Request;
use Drahak\Restful\Http\RequestFactory;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Http\RequestFactory.
 *
 * @testCase Tests\Drahak\Restful\Http\RequestFactoryTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Http
 */
class RequestFactoryTest extends TestCase
{

	/** @var RequestFactory */
	private $factory;

    protected function setUp()
    {
		parent::setUp();
		$this->factory = new RequestFactory('jsonp');
    }
    
    public function testCreateApiRequest()
    {
		$request = $this->factory->createHttpRequest();
		Assert::true($request instanceof Request);
    }

}
\run(new RequestFactoryTest());