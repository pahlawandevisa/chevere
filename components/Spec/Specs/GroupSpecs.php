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

namespace Chevere\Components\Spec\Specs;

use Chevere\Components\DataStructures\Traits\DsMapTrait;
use Chevere\Components\Spec\Specs\GroupSpec;

final class GroupSpecs
{
    use DsMapTrait;

    public function put(GroupSpec $groupSpec): void
    {
        $key = $groupSpec->key();
        $this->map->put($key, $groupSpec);
    }

    public function hasKey(string $key): bool
    {
        return $this->map->hasKey(/** @scrutinizer ignore-type */ $key);
    }

    public function get(string $key): GroupSpec
    {
        /**
         * @var GroupSpec $return
         */
        $return = $this->map->get($key);

        return $return;
    }
}
