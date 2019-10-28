<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Middleware;

use Chevere\Contracts\App\MiddlewareRunnerContract;
use Chevere\Contracts\Middleware\MiddlewareContract;

abstract class Middleware implements MiddlewareContract
{
    /**
     * Dummy method to avoid constructors as a new MiddlewareContract is created in MiddlewareRunner.
     */
    final public function __construct()
    {
    }

    // abstract public function handle(MiddlewareRunnerContract $runner): MiddlewareRunnerContract;
}
