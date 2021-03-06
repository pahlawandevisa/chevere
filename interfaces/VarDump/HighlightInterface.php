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

namespace Chevere\Interfaces\VarDump;

use Chevere\Interfaces\Type\TypeInterface;

interface HighlightInterface
{
    const KEYS = [
        TypeInterface::STRING,
        TypeInterface::FLOAT,
        TypeInterface::INTEGER,
        TypeInterface::BOOLEAN,
        TypeInterface::NULL,
        TypeInterface::OBJECT,
        TypeInterface::ARRAY,
        TypeInterface::RESOURCE,
        VarDumperInterface::FILE,
        VarDumperInterface::CLASS_REG,
        VarDumperInterface::OPERATOR,
        VarDumperInterface::FUNCTION,
        VarDumperInterface::MODIFIERS,
        VarDumperInterface::VARIABLE,
        VarDumperInterface::EMPHASIS,
    ];

    public function __construct(string $key);

    public function wrap(string $dump): string;
}
