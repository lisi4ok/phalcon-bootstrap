# Phalcon Bootstrap

Phalcon Bootstrap application library

## Get Started

### Requirements

To run this application on your machine, you need at least:

* [PHP][1] >= 8.1+
* Server Any of the following
    * [Apache][2] Web Server with [mod_rewrite][3] enabled
    * [Nginx][4] Web Server
* [Phalcon Framework release][5] 5+ extension enabled

## Installation

```
composer require lisi4ok/phalcon-bootstrap
```

## License

Phalcon API is open-sourced software licensed under the [MIT License][7]. ©
* Zaio Klepoyshkov (lisi4ok)
* Phalcon Team
* contributors

## Usage

### Micro applications (Api, prototype or micro service)

First create a config definition file inside your Phalcon project. This file should include the configuration settings, service & middleware definitions and a path to your handlers.

To get started, let's assume the following project structure:

```
├── public
│   ├── index.php
├── src
│   ├── config
│   │    │── config.php
│   │    │── handlers.php
│   │── Controllers
│   │── Domain
│   │── Middleware
│   │── Service
│   │── var
│   │    │── log
├── tests
├── vendor
├── Boot.php
├── composer.json
├── .gitignore
├── README.md
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

use Foo\Middleware\NotFoundMiddleware;

return [
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'debug' => true,
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'var' .
        DIRECTORY_SEPARATOR . 'log' .
        DIRECTORY_SEPARATOR,
    'handlerPath' => __DIR__ . DIRECTORY_SEPARATOR . 'handlers.php',
    'middleware' => [
        NotFoundMiddleware::class => 'before',
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
    ],
    'timezone' => 'Europe\London',
];
```

The **handlerPath** declaration must include your handlers; the best strategy is to utilize Phalcon collections. The contents of this file might look something like this:

```php
<?php

use Foo\Controllers\Index;
use Phalcon\Mvc\Micro\Collection;

$handler = new Collection();
$handler->setHandler(Index::class, true);
$handler->setPrefix('/');
$handler->get('/', 'indexAction', 'apiIndex');
$app->mount($handler);
```

Now, create an index file inside the **public** directory and copy-&-paste the following:

```php
<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'Boot.php';
```

Finally, paste the following bootstrap code inside the **Boot.php** file:

```php
<?php
declare(strict_types=1);

use Phalcon\Bootstrap\Bootstrap;
use Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Bootstrap\Enumerations\Application;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

(function () {
    $config = new Config(
        require __DIR__ . '/src/config/config.php'
    );

    $di = (new DiFactory($config))->createDefaultMvc();

    if (extension_loaded('mbstring')) {
        mb_internal_encoding('UTF-8');
        mb_substitute_character('none');
    }

    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);

            // Verbose exception handling for development
            if ($di->get('config')->debug) {
            }

            exit(1);
        }
    );

    return Bootstrap::handle($di)->run($_SERVER['REQUEST_URI'], Application::Micro);
})();
```

### Mvc applications

Create a config definition file inside your Phalcon project. This file should include your configuration settings and service & middleware definitions.

Let's assume the following mvc project structure:

```
├── public
│   ├── index.php
├── src
│   ├── config
│   │    │── config.php
│   │── Controllers
│   │── Domain
│   │── Middleware
│   │── Module
│   │    │── Admin
│   │    │    │── Controllers
│   │    │    │── Form
│   │    │    │── Task
│   │    │    │── View
│   │    │    │── Module.php
│   │── Service
│   │── var
│   │    │── log
├── tests
├── vendor
├── Boot.php
├── composer.json
├── .gitignore
├── README.md
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

return [
    'annotations' => [
        'adapter' => 'Apcu',
        'options' => [
            'lifetime' => 3600 * 24 * 30,
            'prefix' => 'annotations',
        ],
    ],
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'baseUri' => '/',
    'debug' => true,
    'dispatcher' => [
        'defaultAction' => 'index',
        'defaultController' => 'Admin',
        'defaultControllerNamespace' => 'Foo\\Module\\Admin\\Controller',
        'defaultModule' => 'admin',
    ],
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'var' .
        DIRECTORY_SEPARATOR . 'log' .
        DIRECTORY_SEPARATOR,
    'modules' => [
        'admin' => [
            'className' => 'Foo\\Module\\Admin\\Module',
            'path' => dirname(__DIR__) . '/Module/Admin/Module.php',
        ],
    ],
    'middleware' => [
        'Foo\\Middleware\\Bar',
    ],
    'routes' => [
        'admin' => [
            'Foo\Module\Admin\Controller\Admin' => '/admin',
        ],
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
        'Foo\Service\Annotation',
        'Foo\Service\Router',
        'Foo\Service\View',
    ],
    'timezone' => 'Europe\London',
    'useI18n' => true,
    'view' => [
        'defaultPath' => dirname(__DIR__) . '/Module/Admin/View/',
        'compiledPath' => dirname(__DIR__) . '/Cache/Volt/',
        'compiledSeparator' => '_',
    ]
];
```

Now, create an index file inside the **public** directory and paste the following:

```php
<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'Boot.php';
```

Finally, paste the following bootstrap code inside the **Boot.php** file:

```php
<?php
declare(strict_types=1);

use Phalcon\Bootstrap\Bootstrap;
use Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Bootstrap\Enumerations\Application;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

(function () {
    $config = new Config(
        require __DIR__ . '/src/config/config.php'
    );

    $di = (new DiFactory($config))->createDefaultMvc();

    if (extension_loaded('mbstring')) {
        mb_internal_encoding('UTF-8');
        mb_substitute_character('none');
    }

    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);

            // Verbose exception handling for development
            if ($di->get('config')->debug) {
            }

            exit(1);
        }
    );

    return Bootstrap::handle($di)->run($_SERVER['REQUEST_URI'], Application::application);
})();
```

### Console application

Create a config definition file inside your Phalcon project. This file should include your configuration settings and service & middleware definitions.

Let's assume the following project structure:

```
├── src
│   ├── config
│   │    │── config.php
│   │── Domain
│   │── Middleware
│   │── Service
│   │── Task
│   │── var
│   │    │── log
├── tests
├── vendor
├── Cli.php
├── composer.json
├── .gitignore
├── README.md
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **Config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

return [
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'var' .
        DIRECTORY_SEPARATOR . 'log' .
        DIRECTORY_SEPARATOR,
    'debug' => true,
    'dispatcher' => [
        'defaultTaskNamespace' => 'Foo\\Task',
    ],
    'middleware' => [
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
        'Foo\Service\ConsoleOutput',
    ],
    'timezone' => 'Europe\London',
];
```

Finally, paste the following bootstrap code inside the **Cli.php** file:

```php
<?php
declare(strict_types=1);

use Phalcon\Bootstrap\Cli\Bootstrap;
use Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Bootstrap\Enumerations\Application;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

(function () {
    $config = new Config(
        require __DIR__ . '/src/config/config.php'
    );

    $di = (new DiFactory($config))->createDefaultCli();

    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);
            $output = $di->get('consoleOutput');
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            if ($di->get('config')->debug) {
                $output->writeln(sprintf(
                    '<error>Exception thrown in: %s at line %d.</error>',
                    $e->getFile(),
                    $e->getLine())
                );
            }

            exit(1);
        }
    );

    return Bootstrap::handle($di)->run($_SERVER, Application::Cli);
})();
```

## DI container factory

From the examples above you will have noticed that we instantiated Phalcon's factory default mvc or cli container services.

```php
$config = new Config(
    require __DIR__ . '/src/config/config.php'
);

// Micro/Mvc
$di = (new DiFactory($config))->createDefaultMvc();

// Cli
$di = (new DiFactory($config))->createDefaultCli();
```

Naturally, you can override the factory default services by simply defining a service definition in your config file, like so:

```php
<?php
namespace Foo\Config

return [
    'services' => [
        'Foo\Service\Router',
    ]
]

```

Then create the respective service provider and modify its behaviour:

```php
<?php
declare(strict_types=1);

namespace Foo\Service;

use Foo\Exception\OutOfRangeException;
use Phalcon\Config\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\Router as CliService;
use Phalcon\Mvc\Router as MvcRouter;
use Phalcon\Mvc\Router\Annotations as MvcService;

class Router implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'router',
            function () use ($di) {
                $config = $di->get('config');

                if ($config->get('cli')) {
                    $service = new CliService();
                    $service->setDefaultModule($config->dispatcher->defaultTaskModule);
                    return $service;
                }

                if (!$config->has('modules')) {
                    throw new OutOfRangeException('Undefined modules');
                }

                if (!$config->has('routes')) {
                    throw new OutOfRangeException('Undefined routes');
                }

                $service = new MvcService(false);
                $service->removeExtraSlashes(true);
                $service->setDefaultNamespace($config->dispatcher->defaultControllerNamespace);
                $service->setDefaultModule($config->dispatcher->defaultModule);
                $service->setDefaultController($config->dispatcher->defaultController);
                $service->setDefaultAction($config->dispatcher->defaultAction);

                foreach ($config->get('modules')->toArray() ?? [] as $module => $settings) {
                    if (!$config->routes->get($module, false)) {
                        continue;
                    }
                    foreach ($config->get('routes')->{$module}->toArray() ?? [] as $key => $val) {
                        $service->addModuleResource($module, $key, $val);
                    }
                }

                return $service;
            }
        );
    }
}
```

For complete control over the registration of service dependencies, or more generally, the services available in the container, you have two options: firstly, you can use Phalcon's base DI container, which is an empty container; or you can create your own DI container by implementing Phalcon's **Phalcon\Di\DiInterface**. See the following for an example:

```php
use Phalcon\Di;
use Foo\Bar\MyDi;

$config = new Config(
    require __DIR__ . '/src/config/config.php'
);

// Empty DI container
$di = (new DiFactory($config))->create(new Di);

// Custom DI container
$di = (new DiFactory($config))->create(new MyDi);
```

The DI factory **create method** expects an instance of **Phalcon\Di\DiInterface**.

## Application factory

The bootstrap factory will automatically instantiate a Phalcon application and return the response. If you want to bootstrap the application yourself, you can use the application factory directly.

[1]: https://www.php.net/releases/8.1/

[2]: http://httpd.apache.org/

[3]: http://httpd.apache.org/docs/current/mod/mod_rewrite.html

[4]: http://nginx.org/

[5]: https://github.com/phalcon/cphalcon/releases

[6]: https://github.com/phalcon/phalcon-devtools

[7]: https://github.com/lisi4ok/phalcon-api/blob/master/LICENSE