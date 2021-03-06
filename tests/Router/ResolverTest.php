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

use Chevere\Components\Controller\Controller;
use Chevere\Components\Controller\ControllerParameter;
use Chevere\Components\Controller\ControllerParameters;
use Chevere\Components\Controller\ControllerResponse;
use Chevere\Components\Http\Methods\GetMethod;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Route\Route;
use Chevere\Components\Route\RouteEndpoint;
use Chevere\Components\Route\RouteName;
use Chevere\Components\Route\RoutePath;
use Chevere\Components\Route\RouteWildcardMatch;
use Chevere\Components\Router\Resolver;
use Chevere\Components\Router\Routable;
use Chevere\Components\Router\Routed;
use Chevere\Components\Router\RouterCache;
use Chevere\Components\Router\RouterMaker;
use Chevere\Exceptions\Router\RouteNotFoundException;
use Chevere\Exceptions\Router\RouterException;
use Chevere\Interfaces\Controller\ControllerArgumentsInterface;
use Chevere\Interfaces\Controller\ControllerParametersInterface;
use Chevere\Interfaces\Controller\ControllerResponseInterface;
use Chevere\Interfaces\Router\RouterInterface;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;

final class ResolverTest extends TestCase
{
    private CacheHelper $cacheHelper;

    private array $routes;

    private RouterInterface $router;

    public function setUp(): void
    {
        $this->cacheHelper = new CacheHelper(__DIR__, $this);
        $routerMaker = new RouterMaker;
        $routeEndpoint = new RouteEndpoint(new GetMethod, new ResolverTestController);
        $this->routes = [
            new Route(new RouteName('route-1'), new RoutePath('/test')),
            new Route(new RouteName('route-2'), new RoutePath('/test/{id}')),
            new Route(new RouteName('route-3'), new RoutePath('/test/path')),
        ];
        /** @var Route $route */
        foreach ($this->routes as &$route) {
            $route = $route->withAddedEndpoint($routeEndpoint);
            $routerMaker = $routerMaker->withAddedRoutable(
                new Routable($route),
                'group'
            );
        }
        $this->router = $routerMaker->router();
    }

    public function tearDown(): void
    {
        $this->cacheHelper->tearDown();
    }

    public function testRouteNotFound(): void
    {
        $resolver = new Resolver(
            new RouterCache($this->cacheHelper->getCachedCache())
        );
        $this->expectException(RouteNotFoundException::class);
        $resolver->resolve(new Uri('/404'));
    }

    public function testUnableToResolve(): void
    {
        $resolver = new Resolver(
            new RouterCache($this->cacheHelper->getEmptyCache())
        );
        $this->expectException(RouterException::class);
        $resolver->resolve(new Uri('/test'));
    }

    public function testResolver(): void
    {
        $resolver = new Resolver(
            new RouterCache($this->cacheHelper->getCachedCache())
        );
        $uris = [
            new Uri('/test'),
            new Uri('/test/123'),
            new Uri('/test/path')
        ];
        $arguments = [
            [],
            ['id' => '123'],
            []
        ];
        /**
         * @var int $pos
         * @var RouteInterface $route
         */
        foreach ($this->routes as $pos => $route) {
            $this->assertEquals(
                new Routed($route->name(), $arguments[$pos]),
                $resolver->resolve($uris[$pos])
            );
        }
    }

    public function _testGenerateCached(): void
    {
        $this->expectNotToPerformAssertions();
        $cache = new RouterCache($this->cacheHelper->getCachedCache());
        $cache->withPut($this->router);
    }
}

class ResolverTestController extends Controller
{
    public function getParameters(): ControllerParametersInterface
    {
        new RouteWildcardMatch('[0-9]+');

        return (new ControllerParameters)
            ->withParameter(new ControllerParameter('id', new Regex('/^[0-9]+$/')));
    }

    public function run(ControllerArgumentsInterface $arguments): ControllerResponseInterface
    {
        return new ControllerResponse(true);
    }
}
