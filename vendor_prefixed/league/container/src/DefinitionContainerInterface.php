<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container;

use WPConnectr\ThirdParty\League\Container\Definition\DefinitionInterface;
use WPConnectr\ThirdParty\League\Container\Inflector\InflectorInterface;
use WPConnectr\ThirdParty\League\Container\ServiceProvider\ServiceProviderInterface;
use WPConnectr\ThirdParty\Psr\Container\ContainerInterface;

interface DefinitionContainerInterface extends ContainerInterface
{
    public function add(string $id, $concrete = null): DefinitionInterface;
    public function addServiceProvider(ServiceProviderInterface $provider): self;
    public function addShared(string $id, $concrete = null): DefinitionInterface;
    public function extend(string $id): DefinitionInterface;
    public function getNew($id);
    public function inflector(string $type, callable $callback = null): InflectorInterface;
}
