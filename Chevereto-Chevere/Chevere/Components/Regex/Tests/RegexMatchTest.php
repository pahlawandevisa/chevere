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

namespace Chevere\Components\Regex\Tests;

use Chevere\Components\Regex\Exceptions\RegexMatchException;
use Chevere\Components\Regex\RegexMatch;
use PHPUnit\Framework\TestCase;

final class RegexMatchTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $this->expectException(RegexMatchException::class);
        new RegexMatch('(test)');
    }

    public function testConstructInvalidArgument2(): void
    {
        $this->expectException(RegexMatchException::class);
        new RegexMatch('te(s)t');
    }

    public function testConstruct(): void
    {
        $regexMatchString = '[a-z]+';
        $regexMath = new RegexMatch($regexMatchString);
        $this->assertSame($regexMatchString, $regexMath->toString());
    }
}
