<?php
namespace Drahak\Restful\DI;

use Drahak\Restful\Application\Routes\ResourceRoute;
use Drahak\Restful\IResource;
use Nette\Caching\Storages\FileStorage;
use Nette\Config\CompilerExtension;
use Nette\Config\Configurator;
use Nette\DI\Statement;
use Nette\Diagnostics\Debugger;
use Nette\Loaders\RobotLoader;

// Support newer Nette version
if (class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\DI\CompilerExtension', 'Nette\Config\CompilerExtension');
}

/**
 * Drahak\Restful Extension
 * @package Drahak\Restful\DI
 * @author Drahomír Hanák
 */
class Extension extends CompilerExtension
{

	/**
	 * Default DI settings
	 * @var array
	 */
	protected $defaults = array(
		'cacheDir' => '%tempDir%/cache',
		'jsonpKey' => 'jsonp',
		'routes' => array(
			'presentersRoot' => '%appDir%',
			'autoGenerated' => TRUE,
			'module' => '',
			'panel' => TRUE
		),
		'security' => array(
			'privateKey' => NULL,
			'requestTimeKey' => 'timestamp',
			'requestTimeout' => 300,
		)
	);

	/**
	 * Load DI configuration
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$container->addDefinition($this->prefix('responseFactory'))
			->setClass('Drahak\Restful\ResponseFactory');

		$container->addDefinition($this->prefix('resourceFactory'))
			->setClass('Drahak\Restful\ResourceFactory');
		$container->addDefinition($this->prefix('resource'))
			->setFactory($this->prefix('@resourceFactory') . '::create');

		// Mappers
		$container->addDefinition($this->prefix('xmlMapper'))
			->setClass('Drahak\Restful\Mapping\XmlMapper');
		$container->addDefinition($this->prefix('jsonMapper'))
			->setClass('Drahak\Restful\Mapping\JsonMapper');
		$container->addDefinition($this->prefix('queryMapper'))
			->setClass('Drahak\Restful\Mapping\QueryMapper');
		$container->addDefinition($this->prefix('dataUrlMapper'))
			->setClass('Drahak\Restful\Mapping\DataUrlMapper');

		$container->addDefinition($this->prefix('mapperContext'))
			->setClass('Drahak\Restful\Mapping\MapperContext')
			->addSetup('$service->addMapper(?, ?)', array(IResource::XML, $this->prefix('@xmlMapper')))
			->addSetup('$service->addMapper(?, ?)', array(IResource::JSON, $this->prefix('@jsonMapper')))
			->addSetup('$service->addMapper(?, ?)', array(IResource::QUERY, $this->prefix('@queryMapper')))
			->addSetup('$service->addMapper(?, ?)', array(IResource::DATA_URL, $this->prefix('@dataUrlMapper')));

		// Add input parser
		$container->addDefinition($this->prefix('input'))
			->setClass('Drahak\Restful\Input');

		// Annotation parsers
		$container->addDefinition($this->prefix('routeAnnotation'))
			->setClass('Drahak\Restful\Application\RouteAnnotation');

		// Security
		$container->addDefinition($this->prefix('hashCalculator'))
			->setClass('Drahak\Restful\Security\HashCalculator')
			->setArguments(array($this->prefix('@queryMapper')))
			->addSetup('$service->setPrivateKey(?)', array($this->config['security']['privateKey']));

		$container->addDefinition($this->prefix('hashAuthenticator'))
			->setClass('Drahak\Restful\Security\Authentication\HashAuthenticator')
			->setArguments(array($config['security']['privateKey']));
		$container->addDefinition($this->prefix('timeoutAuthenticator'))
			->setClass('Drahak\Restful\Security\Authentication\TimeoutAuthenticator')
			->setArguments(array($config['security']['requestTimeKey'], $config['security']['requestTimeout']));

		$container->addDefinition($this->prefix('nullAuthentication'))
			->setClass('Drahak\Restful\Security\NullAuthentication');
		$container->addDefinition($this->prefix('securedAuthentication'))
			->setClass('Drahak\Restful\Security\SecuredAuthentication');

		$container->addDefinition($this->prefix('authentication'))
			->setClass('Drahak\Restful\Security\AuthenticationContext')
			->addSetup('$service->setAuthProcess(?)', array($this->prefix('@nullAuthentication')));

		// Generate routes from presenter annotations
		if ($config['routes']['autoGenerated']) {
			$container->addDefinition($this->prefix('routeListFactory'))
				->setClass('Drahak\Restful\Application\Routes\RouteListFactoryProxy')
				->setArguments(array($config['routes']));

			$container->getDefinition('router')
				->addSetup('offsetSet', array(
					NULL,
					new Statement($this->prefix('@routeListFactory') . '::create')
				));
		}

		// Create resource routes debugger panel
		if ($config['routes']['panel']) {
			$container->addDefinition($this->prefix('panel'))
				->setClass('Drahak\Restful\Diagnostics\ResourceRouterPanel')
				->setArguments(array(
					$this->config['security']['privateKey'],
					isset($this->config['security']['requestTimeKey']) ? $this->config['security']['requestTimeKey'] : 'timestamp'
				))
				->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array('@self'));

			$container->getDefinition('application')
				->addSetup('$service->onStartup[] = ?', array(array($this->prefix('@panel'), 'getTab')));
		}

		$container->addDefinition($this->prefix('methodHandler'))
			->setClass('Drahak\Restful\Application\MethodHandler');

		$container->getDefinition('httpRequest')
			->setClass('Drahak\Restful\Http\IRequest');

		$container->getDefinition('nette.httpRequestFactory')
			->setClass('Drahak\Restful\Http\RequestFactory')
			->setArguments(array($config['jsonpKey']));

		$container->getDefinition('application')
			->addSetup('$service->onStartup[] = ?', array(array($this->prefix('@methodHandler'), 'run')));
	}


	/**
	 * Register REST API extension
	 * @param Configurator $configurator
	 */
	public static function install(Configurator $configurator)
	{
		$configurator->onCompile[] = function($configurator, $compiler) {
			$compiler->addExtension('restful', new Extension);
		};
	}

}