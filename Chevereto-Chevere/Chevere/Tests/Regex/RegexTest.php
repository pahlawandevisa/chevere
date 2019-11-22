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

namespace Chevere\Tests\Regex;

use Chevere\Components\Regex\Exceptions\RegexException;
use Chevere\Components\Regex\Regex;
use PHPUnit\Framework\TestCase;

final class RegexTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $this->expectException(RegexException::class);
        new Regex('$');
    }

    public function testConstruct(): void
    {
        $regexString = '/test/';
        $regex = new Regex($regexString);
        $this->assertSame($regexString, $regex->toString());
    }
}
