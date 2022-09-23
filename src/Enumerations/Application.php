<?php
declare(strict_types=1);

namespace Phalcon\Bootstrap\Enumerations;

enum Application: string {
    case Micro = 'micro';
    case Cli = 'cli';
    case Application = 'application';
}