<?php
namespace Drahak\Restful;

/**
 * IResource determines REST service result set
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IResource extends IDataResource
{

	/** Result types */
	const XML = 'application/xml';
	const JSON = 'application/json';
	const JSONP = 'application/javascript';
	const QUERY = 'text/x-query';
	const DATA_URL = 'application/x-data-url';
	const NULL = 'NULL';

	/**
	 * Get content type
	 * @return string
	 */
	public function getContentType();

}