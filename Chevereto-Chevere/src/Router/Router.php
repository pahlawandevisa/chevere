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

namespace Chevere\Router;

use Chevere\Message;
use Chevere\Cache\Cache;
use Chevere\Cache\Exceptions\CacheNotFoundException;
use Chevere\Contracts\Route\RouteContract;
use Chevere\Contracts\Router\RouterContract;
use Chevere\FileReturn\Exceptions\FileNotFoundException;
use Chevere\Router\Exception\RouteNotFoundException;

/**s
 * Routes takes a bunch of Routes and generates a routing table (php array).
 */
final class Router implements RouterContract
{
    const REGEX_TEPLATE = '#^(?%s)$#x';

    /** @var string Regex representation, used when resolving routing */
    private $regex;

    /** @var array Route members (objects, serialized) [id => Route] */
    private $routes;

    /** @var array Contains ['/path' => [id, 'route/key']] */
    private $routesIndex;

    /** @var array Arguments taken from wildcard matches */
    private $arguments;

    public function __construct()
    { }

    public static function fromMaker(Maker $maker): RouterContract
    {
        $router = new static();
        $router->regex = $maker->regex();
        $router->routes = $maker->routes();
        $router->routesIndex = $maker->routesIndex();
        $maker->setcache();
        return $router;
    }

    public static function fromCache(): RouterContract
    {
        $router = new static();
        $cache = new Cache('router');
        try {
            $router->regex = $cache->get('regex')->raw();
            $router->routes = $cache->get('routes')->raw();
            $router->routesIndex = $cache->get('routesIndex')->raw();
        } catch (FileNotFoundException $e) {
            throw new CacheNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
        return $router;
    }

    public function arguments(): array
    {
        return $this->arguments ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $pathInfo): RouteContract
    {
        if (preg_match($this->regex, $pathInfo, $matches)) {
            return $this->resolver($matches);
        }
        throw new RouteNotFoundException(
            (new Message('No route defined for %s'))
                ->code('%s', $pathInfo)
                ->toString()
        );
    }

    private function resolver(array $matches): RouteContract
    {
        $id = $matches['MARK'];
        unset($matches['MARK']);
        array_shift($matches);
        $route = $this->routes[$id];
        // Array when the route is a powerSet [id, set]
        if (is_array($route)) {
            $set = $route[1];
            $route = $this->routes[$route[0]];
        }
        if (is_string($route)) {
            $resolver = new Resolver($route);
            $route = $resolver->get();
            $this->routes[$id] = $route;
        }
        $this->arguments = [];
        if (isset($set)) {
            foreach ($matches as $k => $v) {
                $wildcardId = $route->keyPowerSet()[$set][$k];
                $wildcardName = $route->wildcardName($wildcardId);
                $this->arguments[$wildcardName] = $v;
            }
        }

        return $route;
    }
}
