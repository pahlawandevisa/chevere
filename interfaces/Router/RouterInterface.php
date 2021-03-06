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

namespace Chevere\Interfaces\Router;

use Chevere\Components\Router\Routables;

interface RouterInterface
{
    const CACHE_ID = 'router';

    public function withRoutables(Routables $routables): RouterInterface;

    public function routables(): Routables;

    /**
     * Return an instance with the specified RegexInterface.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified RegexInterface.
     */
    public function withRegex(RouterRegexInterface $regex): RouterInterface;

    public function hasRegex(): bool;

    /**
     * Provides access to the instance regex.
     */
    public function regex(): RouterRegexInterface;

    /**
     * Return an instance with the specified index.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified index.
     */
    public function withIndex(RouterIndexInterface $index): RouterInterface;

    /**
     * Provides access to the instance index.
     */
    public function index(): RouterIndexInterface;
}
