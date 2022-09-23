<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Application;

use Phalcon\Cli\Console;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;

interface FactoryInterface
{
    /**
     * Bootstrap a console application, encapsulation
     * module registration, event management and
     * middleware logic assignment.
     */
    public function createCli(): Console;

    /**
     * Bootstrap an api or micro service, encapsulating
     * handler registration, event management and middleware
     * logic assignment.
     */
    public function createMicro(): Micro;

    /**
     * Bootstrap an application following the mvc pattern,
     * encapsulating module registration, event management
     * and middleware logic assignment.
     */
    public function createMvc(): Application;
}
