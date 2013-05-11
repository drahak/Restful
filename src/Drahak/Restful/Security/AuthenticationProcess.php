<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\IInput;
use Nette\Object;

/**
 * Request AuthenticationProcess template
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
abstract class AuthenticationProcess extends Object
{

	/**
	 * Authenticate process
	 * @param IInput $input
	 */
	public final function authenticate(IInput $input)
	{
		$this->authRequestData($input);
		$this->authRequestTime($input);
	}

	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool
	 */
	abstract protected function authRequestData(IInput $input);

	/**
	 * Authenticate request time
	 * @param IInput $input
	 * @return bool
	 */
	abstract protected function authRequestTime(IInput $input);

}