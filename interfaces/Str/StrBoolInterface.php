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

namespace Chevere\Interfaces\Str;

interface StrBoolInterface
{
    public function __construct(string $string);

    public function empty(): bool;

    public function ctypeSpace(): bool;

    public function ctypeDigit(): bool;

    public function startsWithCtypeDigit(): bool;

    public function startsWith(string $needle): bool;

    public function endsWith(string $needle): bool;

    public function same(string $string): bool;
}
