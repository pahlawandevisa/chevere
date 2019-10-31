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

use Chevere\Components\Api\Api;
use Chevere\Components\Api\Maker as ApiMaker;
use Chevere\Components\App\Exceptions\AlreadyBuiltException;
use Chevere\Components\App\Exceptions\NoBuiltFileException;
use Chevere\Components\Cache\Cache;
use Chevere\Components\Dir\Dir;
use Chevere\Components\File\File;
use Chevere\Components\Message\Message;
use Chevere\Components\Path\Path;
use Chevere\Components\Router\Router;
use Chevere\Contracts\App\BuildContract;
use Chevere\Contracts\App\CheckoutContract;
use Chevere\Contracts\App\ServicesContract;
use Chevere\Contracts\App\ParametersContract;
use Chevere\Contracts\Router\MakerContract;
use LogicException;

/**
 * The Build container.
 */
final class Build implements BuildContract
{
    /** @var ServicesContract */
    private $services;

    /** @var ParametersContract */
    private $parameters;

    /** @var File */
    private $file;
    
    /** @var Dir */
    private $cacheDir;

    /** @var bool True if the App was just built */
    private $isMaked;

    /** @var CheckoutContract */
    private $checkout;

    /** @var array Containing the collection of Cache->toArray() data (checksums) */
    private $checksums;

    /** @var ApiMaker */
    private $apiMaker;

    /** @var MakerContract */
    private $routerMaker;

    /**
     * {@inheritdoc}
     */
    public function __construct(ServicesContract $services)
    {
        $this->isMaked = false;
        $this->services = $services;
        $this->file = new File(
            new Path('build/build.php')
        );
        $this->cacheDir = new Dir(
            new Path('build/cache')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withServices(ServicesContract $services): BuildContract
    {
        $new = clone $this;
        $new->services = $services;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function services(): ServicesContract
    {
        return $this->services;
    }

    /**
     * {@inheritdoc}
     */
    public function withParameters(ParametersContract $parameters): BuildContract
    {
        $new = clone $this;
        $new->parameters = $parameters;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameters(): bool
    {
        return isset($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function parameters(): ParametersContract
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function withRouterMaker(MakerContract $maker): BuildContract
    {
        $new = clone $this;
        $new->routerMaker = $maker;

        return $new;
    }

    public function hasRouterMaker(): bool
    {
        return isset($this->routerMaker);
    }

    public function routerMaker(): MakerContract
    {
        return $this->routerMaker;
    }

    public function make(): BuildContract
    {
        $this->assertCanMake();
        $new = clone $this;
        $new->checksums = [];
        if ($new->parameters->hasApi()) {
            $new->makeApi();
        }
        if ($new->parameters->hasRoutes()) {
            $new->makeRouter();
        }
        $new->isMaked = true;
        $new->checkout = new Checkout($new);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function isMaked(): bool
    {
        return $this->isMaked;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(): void
    {
        if (!$this->file->exists()) {
            throw new NoBuiltFileException();
        }
        $this->file->remove();
        if ($this->cacheDir->exists()) {
            $this->cacheDir
                ->removeContents();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function file(): File
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheDir(): Dir
    {
        return $this->cacheDir;
    }
    
    /**
     * {@inheritdoc}
     */
    public function checksums(): array
    {
        return $this->checksums;
    }

    /**
     * {@inheritdoc}
     */
    public function checkout(): CheckoutContract
    {
        return $this->checkout;
    }

    private function assertCanMake(): void
    {
        foreach ([
            'parameters' => ParametersContract::class,
            'routerMaker' => MakerContract::class
        ] as $property => $contract) {
            if (!isset($this->{$property})) {
                $missing[] = (new Message('%s'))->code('%s', $contract)->toString(0);
            }
        }
        if (isset($missing)) {
            throw new LogicException(
                (new Message('Method %method% can be only called when the instance of %className% has %contracts%'))
                    ->code('%method%', __METHOD__)
                    ->code('%className%', __CLASS__)
                    ->strtr('%contracts%', implode(', ', $missing))
                    ->toString()
            );
        }
        if ($this->isMaked) {
            throw new AlreadyBuiltException();
        }
    }

    private function makeApi(): void
    {
        $this->apiMaker = new ApiMaker($this->routerMaker);
        $this->apiMaker = $this->apiMaker
            ->withPath(
                new Path(
                    $this->parameters->api()
                )
            );
        $this->services = $this->services
            ->withApi(
                (new Api())
                    ->withMaker($this->apiMaker)
            );
        $this->apiMaker = $this->apiMaker
            ->withCache(
                new Cache('api', $this->cacheDir)
            );
        $this->checksums = $this->apiMaker->cache()->toArray();
    }

    private function makeRouter(): void
    {
        $this->routerMaker = $this->routerMaker
            ->withAddedRouteFiles(...$this->parameters->routes());
        $this->services = $this->services
            ->withRouter(
                (new Router())
                    ->withMaker($this->routerMaker)
            );
        $this->routerMaker = $this->routerMaker
            ->withCache(
                new Cache('router', $this->cacheDir)
            );
        $this->checksums = array_merge($this->routerMaker->cache()->toArray(), $this->checksums);
    }
}
