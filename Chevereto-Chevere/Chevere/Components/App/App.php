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

use Chevere\Components\Route\Traits\RouteAccessTrait;
use Chevere\Components\Router\Traits\RouterAccessTrait;
use Chevere\Contracts\App\AppContract;
use Chevere\Contracts\Http\ResponseContract;
use Chevere\Contracts\Route\RouteContract;
use Chevere\Contracts\Router\RouterContract;

/**
 * The application container.
 *
 * Provides access to the application, mostly intended for providing access at ControllerContract layer.
 */
final class App implements AppContract
{
    use RouterAccessTrait;
    use RouteAccessTrait;

    /** @var ResponseContract */
    private $response;

    /** @var array String arguments (from request, cli) */
    private $arguments;

    /**
     * {@inheritdoc}
     */
    public function __construct(ResponseContract $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withResponse(ResponseContract $response): AppContract
    {
        $new = clone $this;
        $new->response = $response;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function response(): ResponseContract
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function withRoute(RouteContract $route): AppContract
    {
        $new = clone $this;
        $new->route = $route;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withRouter(RouterContract $router): AppContract
    {
        $new = clone $this;
        $new->router = $router;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withArguments(array $arguments): AppContract
    {
        $new = clone $this;
        $new->arguments = $arguments;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasArguments(): bool
    {
        return isset($this->arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
}