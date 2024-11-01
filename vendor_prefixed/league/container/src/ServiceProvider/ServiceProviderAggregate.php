<?php
/**
 * @license MIT
 *
 * Modified by reenhanced on 22-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPConnectr\ThirdParty\League\Container\ServiceProvider;

use Generator;
use WPConnectr\ThirdParty\League\Container\Exception\ContainerException;
use WPConnectr\ThirdParty\League\Container\{ContainerAwareInterface, ContainerAwareTrait};

class ServiceProviderAggregate implements ServiceProviderAggregateInterface
{
    use ContainerAwareTrait;

    /**
     * @var ServiceProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $registered = [];

    public function add(ServiceProviderInterface $provider): ServiceProviderAggregateInterface
    {
        if (in_array($provider, $this->providers, true)) {
            return $this;
        }

        $provider->setContainer($this->getContainer());

        if ($provider instanceof BootableServiceProviderInterface) {
            $provider->boot();
        }

        $this->providers[] = $provider;
        return $this;
    }

    public function provides(string $service): bool
    {
        foreach ($this->getIterator() as $provider) {
            if ($provider->provides($service)) {
                return true;
            }
        }

        return false;
    }

    public function getIterator(): Generator
    {
        yield from $this->providers;
    }

    public function register(string $service): void
    {
        if (false === $this->provides($service)) {
            throw new ContainerException(
                sprintf('(%s) is not provided by a service provider', $service)
            );
        }

        foreach ($this->getIterator() as $provider) {
            if (in_array($provider->getIdentifier(), $this->registered, true)) {
                continue;
            }

            if ($provider->provides($service)) {
                $provider->register();
                $this->registered[] = $provider->getIdentifier();
            }
        }
    }
}
