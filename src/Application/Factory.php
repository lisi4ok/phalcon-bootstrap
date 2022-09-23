<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Application;

use Phalcon\Bootstrap\Exception\OutOfBoundsException;
use Phalcon\Cli\Console;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;
use Phalcon\Di\DiInterface;

/**
 * A simple application factory, encapsulating module registration,
 * handler registration (micro), event management and middleware logic
 * assignment for full-stack mvc applications, microservices and
 * console applications.
 */
class Factory implements FactoryInterface
{
    public function __construct(private DiInterface $di)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createCli(): Console
    {
        $di = $this->di;
        $app = new Console($di);
        $config = $di->get('config');

        if ($config->has('modules')) {
            $app->registerModules($config->get('modules')?->toArray() ?? []);
        }

        $eventManager = $di->get('eventsManager');

        if ($config->has('middleware')) {
            foreach ($config->get('middleware')?->toArray() ?? [] as $middleware) {
                $instance = new $middleware();
                $eventManager->attach('console', $instance);
            }
        }

        $app->setEventsManager($eventManager);
        $di->setShared('console', $app);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function createMicro(): Micro
    {
        $di = $this->di;
        $app = new Micro($di);
        $config = $di->get('config');
        $handlers = $config->path('handlerPath', null);

        if (is_null($handlers) || !file_exists($handlers)) {
            throw new OutOfBoundsException('Could not load application handlers');
        }

        if (!is_file($handlers)) {
            throw new OutOfBoundsException('Could not find application handlers');
        }

        require $handlers;

        $eventManager = $di->get('eventsManager');
        $eventManager->enablePriorities(true);

        if ($config->has('middleware')) {
            foreach ($config->get('middleware')?->toArray() ?? [] as $middleware => $event) {
                $instance = new $middleware();
                $priority = $eventManager::DEFAULT_PRIORITY;

                if (is_array($event)) {
                    list($event, $priority) = $event;
                }

                $eventManager->attach('micro', $instance, $priority);
                $app->{$event}($instance);
            }
        }

        $app->setEventsManager($eventManager);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function createMvc(): Application
    {
        $di = $this->di;
        $app = new Application($di);
        $config = $di->get('config');
        $app->useImplicitView($config->has('view') ?? false);

        if ($config->has('modules')) {
            $app->registerModules($config->get('modules')?->toArray() ?? []);
        }

        $eventManager = $di->get('eventsManager');

        if ($config->has('middleware')) {
            foreach ($config->get('middleware')?->toArray() ?? [] as $middleware) {
                $instance = new $middleware();
                $eventManager->attach('application', $instance);
            }
        }

        $app->setEventsManager($eventManager);

        return $app;
    }
}
