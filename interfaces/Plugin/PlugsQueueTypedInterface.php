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

interface PlugsQueueTypedInterface
{
    public function withAdded(PlugInterface $plug): PlugsQueueTypedInterface;

    /**
     * @return string The accepted plug interface.
     */
    public function accept(): string;

    public function getPlugType(): PlugTypeInterface;

    public function plugsQueue(): PlugsQueueInterface;
}
