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

use Chevere\Components\Cache\CacheKey;
use Chevere\Interfaces\Cache\CacheInterface;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Router\RouteCacheNotFoundException;
use Chevere\Exceptions\Router\RouteCacheTypeException;
use Chevere\Interfaces\Router\RoutableInterface;
use Chevere\Interfaces\Router\RouteResolvesCacheInterface;
use Chevere\Components\Type\Type;
use Chevere\Components\VarExportable\VarExportable;
use Throwable;

final class RouteResolvesCache implements RouteResolvesCacheInterface
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function has(int $id): bool
    {
        return $this->cache->exists(new CacheKey((string) $id));
    }

    public function get(int $id): RouteResolve
    {
        $idString = (string) $id;
        try {
            $item = $this->cache->get(new CacheKey($idString));
        } catch (Throwable $e) {
            throw new RouteCacheNotFoundException(
                (new Message('Cache not found for route %routeName%'))
                    ->strong('%routeName%', $idString)
            );
        }
        if ((new Type(RouteResolve::class))->validate($item->var()) === false) {
            throw new RouteCacheTypeException(
                (new Message('Expecting object implementing %expected%, %provided% provided in route %id%'))
                    ->code('%expected%', RoutableInterface::class)
                    ->code('%provided%', gettype($item->raw()))
                    ->strong('%id%', $idString)
            );
        }

        return $item->var();
    }

    public function put(int $id, RouteResolve $routeResolve): void
    {
        $this->cache = $this->cache
            ->withPut(
                new CacheKey((string) $id),
                new VarExportable($routeResolve)
            );
    }

    public function remove(int $id): void
    {
        $this->cache = $this->cache
            ->withRemove(
                new CacheKey((string) $id)
            );
    }

    public function puts(): array
    {
        return $this->cache->puts();
    }
}
