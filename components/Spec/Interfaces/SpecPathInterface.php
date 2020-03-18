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

namespace Chevere\Components\Spec\Interfaces;

use Chevere\Components\Filesystem\Interfaces\Path\PathInterface;

interface SpecPathInterface
{
    public function pub(): string;

    public function getChild(string $child): SpecPathInterface;
}
