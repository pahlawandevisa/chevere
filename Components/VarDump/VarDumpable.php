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

namespace Chevere\Components\VarDump;

use Chevere\Components\Message\Message;
use Chevere\Interfaces\VarDump\ProcessorInterface;
use Chevere\Interfaces\VarDump\VarDumpableInterface;
use Chevere\Interfaces\VarDump\VarDumperInterface;
use LogicException;
use function ChevereFn\varType;

/**
 * The Chevere VarDumpable.
 * Provides interaction for dumpable variables.
 */
final class VarDumpable implements VarDumpableInterface
{
    /** @var mixed */
    private $var;

    private string $type;

    private string $processorName;

    /**
     * @throws LogicException if it is not possible to dump the passed variable.
     */
    public function __construct($var)
    {
        $this->var = $var;
        $this->type = varType($this->var);
        $this->assertSetProcessorName();
    }

    public function var()
    {
        return $this->var;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function processorName(): string
    {
        return $this->processorName;
    }

    private function assertSetProcessorName(): void
    {
        $processorName = VarDumperInterface::PROCESSORS[$this->type] ?? null;
        if (!isset($processorName)) {
            // @codeCoverageIgnoreStart
            throw new LogicException(
                (new Message('No processor for variable of type %type%'))
                    ->code('%type%', $this->type)
                    ->toString()
            );
            // @codeCoverageIgnoreEnd
        }
        if (!is_subclass_of($processorName, ProcessorInterface::class, true)) {
            // @codeCoverageIgnoreStart
            throw new LogicException(
                (new Message('Processor %processorName% must implement the %interfaceName% interface'))
                    ->code('%processorName%', $processorName)
                    ->code('%interfaceName%', ProcessorInterface::class)
                    ->toString()
            );
            // @codeCoverageIgnoreEnd
        }
        $this->processorName = $processorName;
    }
}