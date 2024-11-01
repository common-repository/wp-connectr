<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\Definition;

use WPConnectr\ThirdParty\League\Container\ContainerAwareInterface;

interface DefinitionInterface extends ContainerAwareInterface
{
    public function addArgument($arg): DefinitionInterface;
    public function addArguments(array $args): DefinitionInterface;
    public function addMethodCall(string $method, array $args = []): DefinitionInterface;
    public function addMethodCalls(array $methods = []): DefinitionInterface;
    public function addTag(string $tag): DefinitionInterface;
    public function getAlias(): string;
    public function getConcrete();
    public function hasTag(string $tag): bool;
    public function isShared(): bool;
    public function resolve();
    public function resolveNew();
    public function setAlias(string $id): DefinitionInterface;
    public function setConcrete($concrete): DefinitionInterface;
    public function setShared(bool $shared): DefinitionInterface;
}
