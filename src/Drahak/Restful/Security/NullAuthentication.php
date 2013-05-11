<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\IInput;

/**
 * NullAuthentication for non-secured API requests
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class NullAuthentication extends AuthenticationProcess
{
	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestData(IInput $input)
	{
		return TRUE;
	}

	/**
	 * Authenticate request time
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestTime(IInput $input)
	{
		return TRUE;
	}


}