<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\Inflector;

interface InflectorInterface
{
    public function getType(): string;
    public function inflect(object $object): void;
    public function invokeMethod(string $name, array $args): InflectorInterface;
    public function invokeMethods(array $methods): InflectorInterface;
    public function setProperties(array $properties): InflectorInterface;
    public function setProperty(string $property, $value): InflectorInterface;
}
