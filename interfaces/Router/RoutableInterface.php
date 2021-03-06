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

namespace Chevere\Interfaces\Router;

use Chevere\Interfaces\Route\RouteInterface;
use Chevere\Exceptions\Router\RouteNotRoutableException;
use Chevere\Exceptions\Router\RouteWithoutEndpointsException;

/**
 * Determines if a RouteInterface is able to be routed.
 */
interface RoutableInterface
{
    /**
     * @throws RouteNotRoutableException
     * @throws RouteWithoutEndpointsException
     */
    public function __construct(RouteInterface $route);

    /**
     * Provides access to the RouteInterface instance.
     */
    public function route(): RouteInterface;
}
