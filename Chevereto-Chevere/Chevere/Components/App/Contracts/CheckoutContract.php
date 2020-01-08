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

namespace Chevere\Components\App\Contracts;

interface CheckoutContract
{
    /**
     * The checkout consists in the creation of a build file which maps the build checksums.
     */
    public function __construct(BuildContract $build);

    /**
     * Get the build file checksum.
     */
    public function checksum(): string;
}