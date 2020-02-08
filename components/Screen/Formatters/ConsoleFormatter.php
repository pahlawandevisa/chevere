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

namespace Chevere\Components\Screen\Formatters;

use Chevere\Components\Screen\Interfaces\FormatterInterface;
use JakubOnderka\PhpConsoleColor\ConsoleColor;

final class ConsoleFormatter implements FormatterInterface
{
    private string $char;

    public function __construct()
    {
        $this->char = (new ConsoleColor)->apply('reverse', '%');
    }

    public function wrap(string $display): string
    {
        return $this->char . $display . $this->char;
    }
}