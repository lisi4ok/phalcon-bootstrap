<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Cli;

use Phalcon\Di\DiInterface;
use Phalcon\Cli\TaskInterface;

interface BootstrapInterface
{
    /**
     * Return an instance of the bootstrap.
     */
    public static function handle(DiInterface $di): BootstrapInterface;

    /**
     * Run the console application and return the response.
     */
    public function run(array $server, ?string $context = null): bool|TaskInterface;
}
