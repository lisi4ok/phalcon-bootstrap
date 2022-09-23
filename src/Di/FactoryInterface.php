<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Di;

use Phalcon\Di\DiInterface;

interface FactoryInterface
{
    /**
     * Return an instance of a dependency injection container,
     * automatically registering service dependency definitions.
     */
    public function create(DiInterface $di): DiInterface;

    /**
     * Create an instance of Phalcon's factory default dependency
     * injection container for the mvc environment.
     */
    public function createDefaultMvc(): DiInterface;

    /**
     * Create an instance of Phalcon's factory default dependency
     * injection container for the cli environment.
     */
    public function createDefaultCli(): DiInterface;
}
