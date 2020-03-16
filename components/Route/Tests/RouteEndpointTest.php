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

namespace Chevere\Components\Route\Tests;

use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Interfaces\Dir\DirInterface;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Http\Methods\GetMethod;
use Chevere\Components\Route\Exceptions\EndpointException;
use PHPUnit\Framework\TestCase;

final class RouteEndpointTest extends TestCase
{
    private DirInterface $resourcesDir;

    public function setUp(): void
    {
        $this->resourcesDir = (new Dir(new Path(__DIR__ . '/')))->getChild('_resources/');
    }
}