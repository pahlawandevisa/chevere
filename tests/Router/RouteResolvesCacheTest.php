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

namespace Chevere\Tests\Router;

use Chevere\Components\Route\Route;
use Chevere\Components\Route\RouteName;
use Chevere\Components\Route\RoutePath;
use Chevere\Components\Router\RouteResolve;
use Chevere\Components\Router\RouteResolvesCache;
use Chevere\Exceptions\Router\RouteCacheNotFoundException;
use Chevere\Exceptions\Router\RouteCacheTypeException;
use PHPUnit\Framework\TestCase;

final class RouteResolvesCacheTest extends TestCase
{
    private CacheHelper $cacheHelper;

    private array $routes;

    public function setUp(): void
    {
        $this->cacheHelper = new CacheHelper(__DIR__, $this);
        $this->routes = [
            new Route(new RouteName('route-1'), new RoutePath('/test')),
            new Route(new RouteName('route-2'), new RoutePath('/test/{id}')),
            new Route(new RouteName('route-3'), new RoutePath('/test/path')),
        ];
    }

    public function tearDown(): void
    {
        $this->cacheHelper->tearDown();
    }

    public function testEmptyCache(): void
    {
        $resolverCache = new RouteResolvesCache($this->cacheHelper->getEmptyCache());
        /** @var int $id */
        $keys = array_keys($this->routes);
        foreach ($keys as $id) {
            $this->assertFalse($resolverCache->has($id));
        }
        $this->assertEmpty($resolverCache->puts());
        $this->expectException(RouteCacheNotFoundException::class);
        $resolverCache->get($keys[0]);
    }

    public function testWorkingCache(): void
    {
        $resolvesCache = new RouteResolvesCache($this->cacheHelper->getWorkingCache());
        /**
         * @var int $pos
         * @var Route $route
         */
        foreach ($this->routes as $pos => $route) {
            $routeResolve = new RouteResolve(
                $route->name(),
                $route->path()->wildcards()
            );
            $resolvesCache->put($pos, $routeResolve);
            $this->assertArrayHasKey($pos, $resolvesCache->puts());
            $this->assertEquals(
                $routeResolve,
                $resolvesCache->get(/** @scrutinizer ignore-type */$pos)
            );
            $resolvesCache->remove($pos);
            $this->assertArrayNotHasKey($pos, $resolvesCache->puts());
        }
    }

    public function testCachedCache(): void
    {
        $resolverCache = new RouteResolvesCache($this->cacheHelper->getCachedCache());
        /**
         * @var int $pos
         * @var Route $route
         */
        foreach ($this->routes as $pos => $route) {
            $this->assertTrue($resolverCache->has($pos));
            $routeResolve = new RouteResolve(
                $route->name(),
                $route->path()->wildcards()
            );
            $this->assertEquals($routeResolve, $resolverCache->get($pos));
        }
    }

    public function testWrongCachedCache(): void
    {
        $pos = 0;
        $resolverCache = new RouteResolvesCache($this->cacheHelper->getWrongCache());
        $this->assertTrue($resolverCache->has($pos));
        $this->expectException(RouteCacheTypeException::class);
        $resolverCache->get($pos);
    }

    public function _testGenerateCached(): void
    {
        $this->expectNotToPerformAssertions();
        $resolverCache = new RouteResolvesCache($this->cacheHelper->getCachedCache());
        /**
         * @var int $pos
         * @var Route $route
         */
        foreach ($this->routes as $pos => $route) {
            $routeResolve = new RouteResolve(
                $route->name(),
                $route->path()->wildcards()
            );
            $resolverCache->put($pos, $routeResolve);
        }
    }
}
