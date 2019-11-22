<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Router;

use InvalidArgumentException;
use Chevere\Components\Message\Message;
use Chevere\Contracts\Route\RouteContract;
use Chevere\Contracts\Router\RouteableContract;
use Chevere\Contracts\Router\RouterMakerContract;
use Chevere\Contracts\Router\RouterPropertiesContract;

/**
 * RouterMaker takes a bunch of routes and generates a cache-ready routing table.
 */
final class RouterMaker implements RouterMakerContract
{
    /** @var RouterPropertiesContract */
    private $properties;

    /** @var array [regex => Route id]. */
    private $regexIndex;

    /** @var array [/path/{0} => $id] */
    private $routesKeys;

    /** @var array Named routes [routeName => $id] */
    private $named;

    /** @var RouteContract */
    private $route;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->properties = new RouterProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function properties(): RouterPropertiesContract
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedRoute(RouteableContract $routeable, string $group): RouterMakerContract
    {
        $new = clone $this;
        $new->route = $routeable->route();
        $new->assertUniquePath();
        $new->assertUniqueKey();

        $routes = $new->properties->routes();
        $routes[] = $new->route;

        $id = empty($routes) ? 0 : (array_key_last($routes) + 1);

        $groups = $new->properties()->groups();
        $groups[$group][] = $id;

        $new->routesKeys[$new->route->pathUri()->key()] = $id;
        $routeDetails = [
            'id' => $id,
            'group' => $group,
        ];

        if ($new->route->hasName()) {
            $new->assertUniqueNamed();
            $routeName = $new->route->name()->toString();
            $routeDetails['name'] = $routeName;
            $new->named[$routeName] = $id;
        }

        // n => .. => regex => route
        $new->regexIndex[$new->route->regex()] = $id;

        $index = $new->properties->index();
        $index[$new->route->pathUri()->path()] = $routeDetails;

        $new->properties = $new->properties
            ->withRegex($new->getRegex())
            ->withRoutes($routes)
            ->withIndex($index)
            ->withGroups($groups)
            ->withNamed($new->named);

        return $new;
    }

    private function getRegex(): string
    {
        $regex = [];
        foreach ($this->regexIndex as $k => $v) {
            preg_match('#\^(.*)\$#', $k, $matches);
            $regex[] = '|' . $matches[1] . " (*:$v)";
        }

        return sprintf(RouterMakerContract::REGEX_TEPLATE, implode('', $regex));
    }

    private function assertUniquePath(): void
    {
        $path = $this->route->pathUri()->path();
        $routeIndex = $this->properties->index()[$path] ?? null;
        if (isset($routeIndex)) {
            $routeIndexed = $this->properties->routes()[$routeIndex['id']];
            throw new InvalidArgumentException(
                (new Message('Unable to register route path %path% at %declare% (path already registered at %register%)'))
                    ->code('%path%', $path)
                    ->code('%declare%', $this->route->maker()['fileLine'])
                    ->code('%register%', $routeIndexed->maker()['fileLine'])
                    ->toString()
            );
        }
    }

    private function assertUniqueKey(): void
    {
        $routeId = $this->routesKeys[$this->route->pathUri()->key()] ?? null;
        if (isset($routeId)) {
            $routeIndexed = $this->properties->routes()[$routeId];
            throw new InvalidArgumentException(
                (new Message('Router conflict detected for %path% at %declare% (self-assigned internal key %key% is already reserved by %register%)'))
                    ->code('%path%', $this->route->pathUri()->path())
                    ->code('%declare%', $this->route->maker()['fileLine'])
                    ->code('%key%', $this->route->pathUri()->key())
                    ->code('%register%', $routeIndexed->maker()['fileLine'])
                    ->toString()
            );
        }
    }

    private function assertUniqueNamed(): void
    {
        $namedId = $this->named[$this->route->name()->toString()] ?? null;
        if (isset($namedId)) {
            $name = $this->route->name()->toString();
            $routeExists = $this->properties->routes()[$namedId];
            throw new InvalidArgumentException(
                (new Message('Unable to assign route name %name% for path %path% at %declare% (name assigned to %namedRoutePath% at %register%)'))
                    ->code('%name%', $name)
                    ->code('%path%', $this->route->pathUri()->path())
                    ->code('%declare%', $this->route->maker()['fileLine'])
                    ->code('%namedRoutePath%', $routeExists->pathUri()->path())
                    ->code('%register%', $routeExists->maker()['fileLine'])
                    ->toString()
            );
        }
    }
}
