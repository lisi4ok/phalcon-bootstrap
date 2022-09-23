<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Di;

use Phalcon\Config\ConfigInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

/**
 * A simple factory providing dependency injection container instantiation,
 * encapsulating the registration of service dependency definitions for
 * mvc, micro and cli applications.
 */
class Factory implements FactoryInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createDefaultMvc(): DiInterface
    {
        $di = new FactoryDefault();

        return $this->create($di);
    }

    /**
     * {@inheritDoc}
     */
    public function createDefaultCli(): DiInterface
    {
        $di = new Cli();

        return $this->create($di);
    }

    /**
     * {@inheritDoc}
     */
    public function create(DiInterface $di): DiInterface
    {
        $config = $this->config;
        $config->set('cli', $di instanceof Cli ?? false);
        $di->setShared('config', $config);

        if ($config->has('services')) {
            foreach ($config->get('services')->toArray() ?? [] as $service) {
                $di->register(new $service());
            }
        }

        return $di;
    }
}
