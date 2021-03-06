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

namespace Chevere\Components\Route;

use Chevere\Components\Message\Message;
use Chevere\Components\Middleware\Middlewares;
use Chevere\Exceptions\Core\OverflowException;
use Chevere\Exceptions\Core\RangeException;
use Chevere\Interfaces\Middleware\MiddlewaresInterface;
use Chevere\Interfaces\Route\RouteEndpointInterface;
use Chevere\Interfaces\Route\RouteEndpointsInterface;
use Chevere\Interfaces\Route\RouteInterface;
use Chevere\Interfaces\Route\RouteNameInterface;
use Chevere\Interfaces\Route\RoutePathInterface;
use Chevere\Interfaces\Route\RouteWildcardInterface;
use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Http\Server\MiddlewareInterface;

final class Route implements RouteInterface
{
    private RouteNameInterface $name;

    private RoutePathInterface $routePath;

    /** @var array details about the instance maker */
    private array $maker;

    /** @var array [wildcardName => $endpoint] */
    private array $wildcards;

    private MiddlewaresInterface $middlewareNameCollection;

    private RouteEndpoints $endpoints;

    public function __construct(RouteNameInterface $name, RoutePathInterface $routePath)
    {
        $this->name = $name;
        $this->routePath = $routePath;
        $this->maker = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0];
        $this->endpoints = new RouteEndpoints;
        $this->middlewareNameCollection = new Middlewares;
    }

    public function name(): RouteNameInterface
    {
        return $this->name;
    }

    public function path(): RoutePathInterface
    {
        return $this->routePath;
    }

    public function maker(): array
    {
        return $this->maker;
    }

    public function withAddedEndpoint(RouteEndpointInterface $endpoint): RouteInterface
    {
        if ($this->endpoints->hasKey($endpoint->method()->name())) {
            throw new OverflowException(
                (new Message('Endpoint for method %method% has been already added'))
                    ->code('%method%', $endpoint->method()->name())
            );
        }
        $new = clone $this;
        foreach ($new->routePath->wildcards()->getGenerator() as $wildcard) {
            $new->assertWildcardEndpoint($wildcard, $endpoint);
            $wildcardMustRegex = $new->wildcards[$wildcard->name()] ?? null;
            $regex = $endpoint->controller()->parameters()
                ->get($wildcard->name())->regex();
            if (isset($wildcardMustRegex)) {
                if ($regex->toString() !== $wildcardMustRegex) {
                    throw new RangeException(
                        (new Message('Wildcard %wildcard% parameter regex %regex% (fist defined by %controller%) must be the same for all controllers in this route, regex %regexProvided% by %controllerProvided%'))
                            ->code('%wildcard%', $wildcard->toString())
                            ->code('%regex%', $wildcardMustRegex)
                            ->code('%controller%', $wildcard->toString())
                            ->code('%regexProvided%', $regex->toString())
                            ->code('%controllerProvided%', get_class($endpoint->controller()))
                    );
                }
            } else {
                $new->routePath = $new->routePath
                    ->withWildcard($wildcard->withMatch(
                        new RouteWildcardMatch($regex->toNoDelimitersNoAnchors())
                    ));
                $new->wildcards[$wildcard->name()] = $regex->toString();
            }
            $endpoint = $endpoint->withoutParameter($wildcard->name());
        }
        $new->endpoints = $new->endpoints->withPut($endpoint);

        return $new;
    }

    public function endpoints(): RouteEndpointsInterface
    {
        return $this->endpoints;
    }

    public function withAddedMiddleware(MiddlewareInterface $middleware): RouteInterface
    {
        $new = clone $this;
        $new->middlewareNameCollection = $new->middlewareNameCollection
            ->withAddedMiddleware($middleware);

        return $new;
    }

    public function middlewareNameCollection(): MiddlewaresInterface
    {
        return $this->middlewareNameCollection;
    }

    /**
     * @throws InvalidArgumentException If the controller doesn't take parameters
     * @throws OutOfBoundsException If wildcard binds to inexistent controller parameter name
     */
    private function assertWildcardEndpoint(RouteWildcardInterface $wildcard, RouteEndpointInterface $endpoint): void
    {
        if ($endpoint->controller()->parameters()->map()->count() === 0) {
            throw new InvalidArgumentException(
                (new Message("Controller %controller% doesn't accept any parameter (route wildcard %wildcard%)"))
                    ->code('%controller%', get_class($endpoint->controller()))
                    ->code('%wildcard%', $wildcard->toString())
                    ->toString()
            );
        }
        if (array_key_exists($wildcard->name(), $endpoint->parameters()) === false) {
            $parameters = array_keys($endpoint->parameters());
            throw new OutOfBoundsException(
                (new Message('Wildcard parameter %wildcard% must bind to a one of the known %controller% parameters: %parameters%'))
                    ->code('%wildcard%', $wildcard->toString())
                    ->code('%controller%', get_class($endpoint->controller()))
                    ->code('%parameters%', implode(', ', $parameters))
                    ->toString()
            );
        }
    }
}
