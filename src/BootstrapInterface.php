<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap;

use Phalcon\Di\DiInterface;

interface BootstrapInterface
{
    /**
     * Return an instance of the bootstrap.
     */
    public static function handle(DiInterface $di): BootstrapInterface;

    /**
     * Run the mvc (or micro) application and return the response.
     */
    public function run(string $uri, ?string $context): mixed;
}
