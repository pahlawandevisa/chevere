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

namespace Chevere\Components\App;

use Chevere\Components\Controller\Traits\ControllerNameAccessTrait;
use Chevere\Components\App\Interfaces\BuildInterface;
use Chevere\Components\App\Interfaces\BuilderInterface;

/**
 * The application builder container.
 */
final class Builder implements BuilderInterface
{
    use ControllerNameAccessTrait;

    private BuildInterface $build;

    private array $controllerArguments;

    /**
     * Creates a new Builder instance.
     */
    public function __construct(BuildInterface $build)
    {
        $this->build = $build;
    }

    /**
     * {@inheritdoc}
     */
    public function withBuild(BuildInterface $build): BuilderInterface
    {
        $new = clone $this;
        $new->build = $build;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): BuildInterface
    {
        return $this->build;
    }

    /**
     * {@inheritdoc}
     */
    public function withControllerName(string $controllerName): BuilderInterface
    {
        $new = clone $this;
        $new->controllerName = $controllerName;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withControllerArguments(array $arguments): BuilderInterface
    {
        $new = clone $this;
        $new->controllerArguments = $arguments;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasControllerArguments(): bool
    {
        return isset($this->controllerArguments);
    }

    /**
     * {@inheritdoc}
     */
    public function controllerArguments(): array
    {
        return $this->controllerArguments;
    }
}