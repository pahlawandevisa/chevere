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

namespace Chevere\Interfaces\Route;

use Chevere\Exceptions\Route\RouteNameInvalidException;
use Chevere\Interfaces\Middleware\MiddlewaresInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface
{
    /**
     * @throws RouteNameInvalidException if $name doesn't match REGEX_NAME
     */
    public function __construct(RouteNameInterface $name, RoutePathInterface $routePath);

    /**
     * Provides access to the route name (if any).
     */
    public function name(): RouteNameInterface;

    /**
     * Provides access to the RoutePathInterface instance.
     */
    public function path(): RoutePathInterface;

    /**
     * Provides access to the file maker array.
     */
    public function maker(): array;

    /**
     * Return an instance with the specified added $routeEndpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified added $routeEndpoint.
     *
     * This method should allow to override any previous $routeEndpoint.
     */
    public function withAddedEndpoint(RouteEndpointInterface $routeEndpoint): RouteInterface;

    /**
     * Provides access to the RouteEndpointsInterface instance.
     */
    public function endpoints(): RouteEndpointsInterface;

    /**
     * Return an instance with the specified added $middleware.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified added $middleware.
     */
    public function withAddedMiddleware(MiddlewareInterface $middleware): RouteInterface;

    /**
     * Provides access to the MiddlewareNameCollectionInterface instance.
     */
    public function middlewareNameCollection(): MiddlewaresInterface;
}
