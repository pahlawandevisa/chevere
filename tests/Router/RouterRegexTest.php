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

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Tests\Router;

use Chevere\Components\Regex\Regex;
use Chevere\Components\Router\RouterRegex;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RouterRegexTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $regex = new Regex('#eee#');
        $this->expectException(InvalidArgumentException::class);
        new RouterRegex($regex);
    }

    public function testConstruct(): void
    {
        $regex = new Regex('#^(?|/home/([A-z0-9\\_\\-\\%]+) (*:0)|/ (*:1)|/hello-world (*:2))$#x');
        $routerRegex = new RouterRegex($regex);
        $this->assertSame($regex, $routerRegex->regex());
    }
}
