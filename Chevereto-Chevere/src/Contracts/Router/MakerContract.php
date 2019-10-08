<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Contracts\Router;

use Chevere\Cache\Cache;
use Chevere\Contracts\Route\RouteContract;

interface MakerContract
{
    const REGEX_TEPLATE = '#^(?%s)$#x';

    public function withAddedRoute(RouteContract $route, string $group): MakerContract;

    public function withAddedRouteIdentifiers(array $routeIdentifiers): MakerContract;

    public function regex(): string;

    public function routes(): array;

    public function routesIndex(): array;

    public function withCache(): MakerContract;

    public function cache(): Cache;
}
