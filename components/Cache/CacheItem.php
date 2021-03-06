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

namespace Chevere\Components\Cache;

use Chevere\Interfaces\Cache\CacheItemInterface;
use Chevere\Interfaces\Filesystem\FilePhpReturnInterface;

final class CacheItem implements CacheItemInterface
{
    private FilePhpReturnInterface $phpFileReturn;

    public function __construct(FilePhpReturnInterface $phpFileReturn)
    {
        $this->phpFileReturn = $phpFileReturn;
    }

    public function raw()
    {
        return $this->phpFileReturn->raw();
    }

    public function var()
    {
        return $this->phpFileReturn->var();
    }
}
