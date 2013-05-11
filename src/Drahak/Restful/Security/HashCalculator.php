<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\IInput;
use Drahak\Restful\Mapping\QueryMapper;
use Nette\Object;

/**
 * Default auth token calculator implementation
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 *
 * @property-write string $privateKey
 */
class HashCalculator extends Object implements IAuthTokenCalculator
{

	/** Fingerprint hash algorithm */
	const HASH = 'sha256';

	/** @var string */
	private $privateKey;

	/** @var QueryMapper */
	private $mapper;

	public function __construct(QueryMapper $mapper)
	{
		$this->mapper = $mapper;
	}

	/**
	 * Set hash calculator security private key
	 * @param string $privateKey
	 * @return IAuthTokenCalculator
	 */
	public function setPrivateKey($privateKey)
	{
		$this->privateKey = $privateKey;
		return $this;
	}

	/**
	 * Calculate hash
	 * @param IInput $input
	 * @return string
	 */
	public function calculate(IInput $input)
	{
		$dataString = $this->mapper->parseResponse($input->getData());
		return hash_hmac(self::HASH, $dataString, $this->privateKey);
	}

}