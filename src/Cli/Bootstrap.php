<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Cli;

use Phalcon\Bootstrap\Application\Factory;
use Phalcon\Bootstrap\Application\FactoryInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\TaskInterface;

class Bootstrap implements BootstrapInterface
{
    final public function __construct(private FactoryInterface $factory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function handle(DiInterface $di): BootstrapInterface
    {
        $factory = new Factory($di);

        return new static($factory);
    }

    /**
     * {@inheritDoc}
     */
    public function run(array $server): bool|TaskInterface
    {
        $app = $this->factory->createCli();

        return $app->setArgument($server['argv'])->handle();
    }
}
