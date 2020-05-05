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

namespace Chevere\Components\Router;

use Chevere\Components\Message\Message;
use Chevere\Components\Route\Interfaces\RouteInterface;
use Chevere\Components\Router\Exceptions\RouteableException;
use Chevere\Components\Router\Exceptions\RouteNotRouteableException;
use Chevere\Components\Router\Interfaces\RouteableInterface;
use Chevere\Components\VarExportable\VarExportable;
use Throwable;

/**
 * Determines if a RouteInterface is able to be routed.
 */
final class Routeable implements RouteableInterface
{
    private RouteInterface $route;

    /**
     * Creates a new instance.
     *
     * @throws RouteableException if $route is not routeable
     */
    public function __construct(RouteInterface $route)
    {
        $this->route = $route;
        $this->assertExportable();
        $this->assertMethodControllers();
    }

    public function route(): RouteInterface
    {
        return $this->route;
    }

    private function assertExportable(): void
    {
        try {
            new VarExportable($this->route);
        } catch (Throwable $e) {
            throw new RouteNotRouteableException(
                new Message($e->getMessage()),
                $e->getCode(),
            );
        }
    }

    private function assertMethodControllers(): void
    {
        if ($this->route->endpoints()->count() == 0) {
            throw new RouteableException(
                (new Message("Instance of %className% doesn't contain any method controller"))
                    ->code('%className%', RouteInterface::class)
            );
        }
    }
}
