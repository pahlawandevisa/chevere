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

namespace Chevere\Contracts\Middleware;

use Chevere\Contracts\App\MiddlewareRunnerContract;

interface MiddlewareContract
{
    public function __construct();

    // public function handle(): void;
}
