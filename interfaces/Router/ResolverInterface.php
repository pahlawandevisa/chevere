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

use Chevere\Exceptions\Router\RouteNotFoundException;
use Chevere\Exceptions\Router\RouterException;
use Psr\Http\Message\UriInterface;

interface ResolverInterface
{
    public function __construct(RouterCacheInterface $cache);

    /**
     * Returns a RoutedInterface for the given UriInterface.
     *
     * @throws RouterException        if the router encounters any fatal error (UnserializeException, TypeError, etc)
     * @throws RouteNotFoundException if no route resolves the given UriInterface
     */
    public function resolve(UriInterface $uri): RoutedInterface;
}
