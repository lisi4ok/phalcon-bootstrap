<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap;

use Phalcon\Bootstrap\Application\Factory;
use Phalcon\Bootstrap\Application\FactoryInterface;
use Phalcon\Bootstrap\Enumerations\Application;
use Phalcon\Di\DiInterface;
use Phalcon\Http\ResponseInterface;

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
    public function run(string $uri, ?string $context = null): mixed
    {
        if ($context === Application::Micro) {
            return $this->factory->createMicro()->handle($uri);
        }

        $response = $this->factory->createMvc()->handle($uri);

        if ($response instanceof ResponseInterface) {
            return $response->send();
        }

        return $response;
    }
}
