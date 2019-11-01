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

namespace Chevere\Components\App;

use InvalidArgumentException;
use LogicException;

use Chevere\Components\ArrayFile\ArrayFile;
use Chevere\Components\Message\Message;
use Chevere\Components\Path\Path;
use Chevere\Contracts\App\ParametersContract;

/**
 * Application parameters container.
 */
final class Parameters implements ParametersContract
{
    /**
     * The keys accepted by this class, with the gettype at right side.
     */
    private $types = [
        ParametersContract::KEY_API => 'string',
        ParametersContract::KEY_ROUTES => 'array',
    ];

    /** @var ArrayFile The parameters array used to construct the object */
    private $arrayFile;

    /** @var string */
    private $api;

    /** @var array */
    private $routes;

    /**
     * {@inheritdoc}
     */
    public function __construct(ArrayFile $arrayFile)
    {
        $this->arrayFile = $arrayFile;
        $this->assertKeys();
        $array = $this->arrayFile->toArray();
        if (isset($array[ParametersContract::KEY_API])) {
            $this->api = $array[ParametersContract::KEY_API];
        }
        if (isset($array[ParametersContract::KEY_ROUTES])) {
            $this->routes = $array[ParametersContract::KEY_ROUTES];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedRoutePaths(Path ...$paths): ParametersContract
    {
        $new = clone $this;
        if (!isset($new->routes)) {
            $new->routes = [];
        }
        foreach ($paths as $path) {
            if (in_array($path->absolute(), $new->routes)) {
                throw new InvalidArgumentException(
                    (new Message('Route path %path% was already added'))
                        ->code('%path%', $path->absolute())
                        ->toString()
                );
            }
            $new->routes[] = $path->absolute();
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameters(): bool
    {
        return $this->hasApi() || $this->hasRoutes();
    }

    /**
     * {@inheritdoc}
     */
    public function hasApi(): bool
    {
        return isset($this->api);
    }

    /**
     * {@inheritdoc}
     */
    public function api(): string
    {
        return $this->api;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRoutes(): bool
    {
        return isset($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function routes(): array
    {
        return $this->routes;
    }

    private function assertKeys(): void
    {
        foreach ($this->arrayFile as $key => $val) {
            $this->assertKeyAvailable($key);
            $this->assertKeyType($key, gettype($val));
        }
    }

    /**
     * Throws a LogicException if the key doesn't exists in $parameters.
     *
     * @param string $key The AppParameter key
     */
    private function assertKeyAvailable(string $key): void
    {
        if (!array_key_exists($key, $this->types)) {
            throw new LogicException(
                (new Message('Unrecognized %className% key "%key%"'))
                    ->code('%className%', __CLASS__)
                    ->strtr('%key%', $key)
                    ->toString()
            );
        }
    }

    /**
     * Throws a LogicException if the key type doesn't meet the type in $keys.
     *
     * @param string $key The AppParameter key
     * @param string $key The value type
     */
    private function assertKeyType(string $key, string $gettype): void
    {
        if ($gettype !== $this->types[$key]) {
            throw new LogicException(
                (new Message('Expecting %type% type, %gettype% type provided for key %key% in %path%'))
                    ->code('%type%', $this->types[$key])
                    ->code('%gettype%', $gettype)
                    ->code('%key%', $key)
                    ->code('%path%', $this->arrayFile->path()->absolute())
                    ->toString()
            );
        }
    }
}
