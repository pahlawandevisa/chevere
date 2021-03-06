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

interface ControllerArgumentsInterface
{
    public function arguments(): array;

    public function withArgument(string $name, string $value): ControllerArgumentsInterface;

    public function has(string $name): bool;

    public function get(string $name);
}
