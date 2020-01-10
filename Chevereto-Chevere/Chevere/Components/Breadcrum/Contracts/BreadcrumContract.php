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

namespace Chevere\Components\Breadcrum\Contracts;

use Chevere\Components\Common\Contracts\ToArrayContract;
use Chevere\Components\Common\Contracts\ToStringContract;
use Chevere\Components\Breadcrum\Exceptions\BreadcrumException;

interface BreadcrumContract extends ToArrayContract, ToStringContract
{
    /**
     * Returns a boolean indicating whether the instance has the given position.
     */
    public function has(int $pos): bool;

    /**
     * Returns a boolean indicating whether the instance has any items.
     */
    public function hasAny(): bool;

    /**
     * Returns the current breadcrum position.
     *
     * @throws BreadcrumException if there's no item
     */
    public function pos(): int;

    /**
     * Return an instance with the specified string item added.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified item.
     */
    public function withAddedItem(string $item): BreadcrumContract;

    /**
     * Return an instance with the specified waypoint pos removed.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified waypoint pos removed.
     *
     * @param int $post the waypoint position to remove
     *
     * @throws BreadcrumException if the item specified by $pos doesn't exists
     */
    public function withRemovedItem(int $pos): BreadcrumContract;
}
