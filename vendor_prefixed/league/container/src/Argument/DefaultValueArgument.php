<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\Argument;

class DefaultValueArgument extends ResolvableArgument implements DefaultValueInterface
{
    protected $defaultValue;

    public function __construct(string $value, $defaultValue = null)
    {
        $this->defaultValue = $defaultValue;
        parent::__construct($value);
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
