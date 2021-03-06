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

namespace Chevere\Interfaces\Plugin;

interface AssertPlugInterface
{
    public function __construct(PlugInterface $plug);

    public function type(): PlugTypeInterface;

    public function plug(): PlugInterface;
}
