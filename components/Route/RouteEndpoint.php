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

namespace Chevere\Components\Route;

use Chevere\Components\Controller\Interfaces\ControllerInterface;
use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Http\Interfaces\MethodInterface;
use Chevere\Components\Message\Message;
use Chevere\Components\Route\Exceptions\EndpointException;
use Chevere\Components\Route\Interfaces\RouteEndpointInterface;
use ReflectionClass;

abstract class RouteEndpoint implements RouteEndpointInterface
{
    /** @var string Absoltue path to the endpoint file */
    private string $whereIs;

    /** @var MethodInterface The inherithed method, taken from the file basename */
    private MethodInterface $method;

    abstract public function getController(): ControllerInterface;

    final public function __construct()
    {
        $dirWhereIs = dirname($this->whereIs());
        $this->root = new Dir(new Path($dirWhereIs . '/'));
        $name = basename($this->whereIs(), '.php');
        $method = self::KNOWN_METHODS[$name] ?? null;
        if ($method === null) {
            throw new EndpointException(
                (new Message('Unknown method name %provided% provided (inherithed from %basename%)'))
                    ->code('%provided%', $name)
                    ->code('%basename%', basename($this->whereIs))
                    ->toString()
            );
        }
        $this->method = new $method;
    }

    final public function whereIs(): string
    {
        return $this->whereIs ??= (new ReflectionClass($this))->getFileName();
    }

    final public function method(): MethodInterface
    {
        return $this->method;
    }
}
