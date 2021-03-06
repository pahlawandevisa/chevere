<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Interfaces\Controller;

use Throwable;

interface ControllerExecutedInterface
{
    public function withThrowable(Throwable $throwable): ControllerExecutedInterface;

    public function code(): int;

    public function data(): array;

    public function hasThrowable(): bool;

    public function throwable(): Throwable;
}
