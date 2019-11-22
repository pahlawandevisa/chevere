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

namespace Chevere\Contracts\Router;

interface RouterMakerContract
{
    const REGEX_TEPLATE = '#^(?%s)$#x';

    /**
     * Creates a new instance.
     */
    public function __construct();

    /**
     * Provides access to the RouterPropertiesContract instance.
     */
    public function properties(): RouterPropertiesContract;

    /**
     * Return an instance with the specified added RouteableContract.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified added RouteableContract.
     */
    public function withAddedRoute(RouteableContract $routeable, string $group): RouterMakerContract;
}
