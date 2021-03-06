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

namespace Chevere\Interfaces\To;

interface ToArrayInterface
{
    /**
     * Returns an array, representing the object itself or some of its data/properties.
     */
    public function toArray(): array;
}
