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

namespace Chevere\Interfaces\Plugin\Plugs\Hooks;

interface HooksRunnerInterface
{
    /**
     * Run the registered hooks at the given anchor.
     *
     * @throws RuntimeException If the $argument type changes.
     */
    public function run(string $anchor, &$argument): void;
}
