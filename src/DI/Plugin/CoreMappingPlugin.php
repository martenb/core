<?php

namespace Apitte\Core\DI\Plugin;

use Apitte\Core\Decorator\IDecorator;
use Apitte\Core\Decorator\RequestParametersDecorator;
use Apitte\Core\DI\ApiExtension;
use Apitte\Core\Exception\Logical\InvalidStateException;
use Apitte\Core\Mapping\Parameter\FloatTypeMapper;
use Apitte\Core\Mapping\Parameter\IntegerTypeMapper;
use Apitte\Core\Mapping\Parameter\StringTypeMapper;
use Apitte\Core\Mapping\RequestParameterMapping;

class CoreMappingPlugin extends AbstractPlugin
{

	const PLUGIN_NAME = 'mapping';

	/** @var array */
	protected $defaults = [
		'types' => [
			'int' => IntegerTypeMapper::class,
			'float' => FloatTypeMapper::class,
			'string' => StringTypeMapper::class,
		],
	];

	/**
	 * @param PluginCompiler $compiler
	 */
	public function __construct(PluginCompiler $compiler)
	{
		parent::__construct($compiler);
		$this->name = self::PLUGIN_NAME;
	}

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadPluginConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		if (empty($config['types'])) throw new InvalidStateException('No mapping types provided');

		$builder->addDefinition($this->prefix('request.parameters.decorator'))
			->setFactory(RequestParametersDecorator::class)
			->addTag(ApiExtension::CORE_DECORATOR_TAG, ['priority' => 100, 'type' => IDecorator::HANDLER_BEFORE]);

		$rpm = $builder->addDefinition($this->prefix('request.parameters.mapping'))
			->setFactory(RequestParameterMapping::class);

		foreach ($config['types'] as $type => $mapper) {
			$rpm->addSetup('addMapper', [$type, $mapper]);
		}
	}

}