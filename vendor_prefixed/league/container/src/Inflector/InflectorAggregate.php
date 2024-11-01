<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\Inflector;

use Generator;
use WPConnectr\ThirdParty\League\Container\ContainerAwareTrait;

class InflectorAggregate implements InflectorAggregateInterface
{
    use ContainerAwareTrait;

    /**
     * @var Inflector[]
     */
    protected $inflectors = [];

    public function add(string $type, callable $callback = null): Inflector
    {
        $inflector = new Inflector($type, $callback);
        $this->inflectors[] = $inflector;
        return $inflector;
    }

    public function inflect($object)
    {
        foreach ($this->getIterator() as $inflector) {
            $type = $inflector->getType();

            if ($object instanceof $type) {
                $inflector->setContainer($this->getContainer());
                $inflector->inflect($object);
            }
        }

        return $object;
    }

    public function getIterator(): Generator
    {
        yield from $this->inflectors;
    }
}
