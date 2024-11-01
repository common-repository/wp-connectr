<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\Definition;

use IteratorAggregate;
use WPConnectr\ThirdParty\League\Container\ContainerAwareInterface;

interface DefinitionAggregateInterface extends ContainerAwareInterface, IteratorAggregate
{
    public function add(string $id, $definition): DefinitionInterface;
    public function addShared(string $id, $definition): DefinitionInterface;
    public function getDefinition(string $id): DefinitionInterface;
    public function has(string $id): bool;
    public function hasTag(string $tag): bool;
    public function resolve(string $id);
    public function resolveNew(string $id);
    public function resolveTagged(string $tag): array;
    public function resolveTaggedNew(string $tag): array;
}
