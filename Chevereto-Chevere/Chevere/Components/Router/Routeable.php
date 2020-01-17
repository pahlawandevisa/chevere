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
use Chevere\Components\Router\Exceptions\RouteableException;
use Chevere\Components\Variable\Exceptions\VariableExportException;
use Chevere\Components\Variable\VariableExport;
use Chevere\Components\Route\Interfaces\RouteInterface;
use Chevere\Components\Router\Interfaces\RouteableInterface;

/**
 * Determines if a RouteInterface is able to be routed.
 * @package Chevere\Components\Router
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
        $this->assertMethodControllerNames();
    }

    /**
     * {@inheritdoc}
     */
    public function route(): RouteInterface
    {
        return $this->route;
    }

    private function assertExportable(): void
    {
        try {
            new VariableExport($this->route);
        } catch (VariableExportException $e) {
            throw new RouteableException(
                (new Message('Instance of %className% is not exportable: %message%'))
                    ->code('%className%', RouteInterface::class)
                    ->code('%message%', $e->getMessage())
                    ->toString()
            );
        }
    }

    private function assertMethodControllerNames(): void
    {
        if (!$this->route->hasMethodControllerNameCollection()) {
            throw new RouteableException(
                (new Message("Instance of %className% doesn't contain any method controller"))
                    ->code('%className%', RouteInterface::class)
                    ->toString()
            );
        }
    }
}
