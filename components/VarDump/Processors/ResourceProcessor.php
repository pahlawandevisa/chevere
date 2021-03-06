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

namespace Chevere\Components\VarDump\Processors;

use Chevere\Components\VarDump\Processors\Traits\ProcessorTrait;
use Chevere\Interfaces\Type\TypeInterface;
use Chevere\Interfaces\VarDump\ProcessorInterface;
use Chevere\Interfaces\VarDump\VarDumperInterface;

final class ResourceProcessor implements ProcessorInterface
{
    use ProcessorTrait;

    private string $stringVar = '';

    public function __construct(VarDumperInterface $varDumper)
    {
        $this->varDumper = $varDumper;
        $this->assertType();
        $this->info = 'type=' . get_resource_type($this->varDumper->dumpable()->var());
        $this->stringVar = $this->varDumper->formatter()->highlight(
            $this->type(),
            (string) $this->varDumper->dumpable()->var()
        );
    }

    public function type(): string
    {
        return TypeInterface::RESOURCE;
    }

    public function write(): void
    {
        $this->varDumper->writer()->write(
            implode(' ', [
                $this->stringVar,
                $this->highlightParentheses($this->info)
            ])
        );
    }
}
