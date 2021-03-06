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

use Chevere\Components\Str\StrBool;
use Chevere\Components\VarDump\Processors\Traits\ProcessorTrait;
use Chevere\Components\VarDump\VarDumpable;
use Chevere\Components\VarDump\VarDumper;
use Chevere\Interfaces\Type\TypeInterface;
use Chevere\Interfaces\VarDump\ProcessorInterface;
use Chevere\Interfaces\VarDump\VarDumperInterface;
use Ds\Collection;
use Ds\Map;
use IteratorAggregate;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use Throwable;

final class ObjectProcessor implements ProcessorInterface
{
    use ProcessorTrait;

    private object $var;

    private int $depth = 0;

    private ReflectionObject $reflectionObject;

    private string $className;

    /** @var string[] An array containing object ids */
    private array $knownObjects = [];

    private int $objectId;

    public function __construct(VarDumperInterface $varDumper)
    {
        $this->varDumper = $varDumper;
        $this->assertType();
        $this->var = $this->varDumper->dumpable()->var();
        $this->depth = $this->varDumper->depth() + 1;
        $this->knownObjects = $this->varDumper->known();
        $this->className = get_class($this->var);
        $this->handleNormalizeClassName();
        $this->objectId = spl_object_id($this->var);
        $this->info = $this->className . '#' . $this->objectId;
    }

    public function type(): string
    {
        return TypeInterface::OBJECT;
    }

    public function write(): void
    {
        $this->varDumper->writer()->write(
            $this->varDumper->formatter()
                ->highlight(
                    VarDumperInterface::CLASS_REG,
                    $this->className
                ) .
            $this->varDumper->formatter()
                ->highlight(
                    VarDumperInterface::OPERATOR,
                    '#' . (string) $this->objectId
                )
        );

        if (in_array($this->objectId, $this->knownObjects)) {
            $this->varDumper->writer()->write(
                ' ' .
                $this->highlightOperator($this->circularReference() . ' #' . $this->objectId)
            );

            return;
        }
        if ($this->depth > self::MAX_DEPTH) {
            $this->varDumper->writer()->write(
                ' ' .
                $this->highlightOperator($this->maxDepthReached())
            );

            return;
        }
        $this->knownObjects[] = $this->objectId;
        $this->reflectionObject = new ReflectionObject($this->var);
        if ($this->reflectionObject->implementsInterface(Collection::class)) {
            $this->varDumper->writer()->write(' ');
            (new VarDumper(
                $this->varDumper->writer(),
                $this->varDumper->formatter(),
                new VarDumpable($this->var->toArray())
            ))
                ->withDepth($this->depth)
                ->withIndent(
                    $this->varDumper->indent() > 1 ? $this->varDumper->indent() - 1 : $this->varDumper->indent()
                )
                ->withKnownObjects($this->knownObjects)
                ->withProcessor();
        }
        $this->setProperties();
    }

    private function setProperties(): void
    {
        $properties = [];
        $reflectionClass = $this->reflectionObject;
        do {
            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                try {
                    $value = $property->getValue($this->var);
                } catch (Throwable $e) {
                    $value = null;
                }
                $properties[$property->getName()] = [
                    $property->getName(),
                    implode(' ', Reflection::getModifierNames($property->getModifiers())),
                    $value ?? null,
                ];
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());
        $keys = array_keys($properties);
        foreach ($keys as $name) {
            $this->processProperty(...$properties[$name]);
        }
    }

    private function processProperty(string $name, string $modifiers, $var): void
    {
        $this->varDumper->writer()->write(
            implode(' ', [
                "\n" . $this->varDumper->indentString(),
                $this->varDumper->formatter()->highlight(
                    VarDumperInterface::MODIFIERS,
                    $modifiers
                ),
                $this->varDumper->formatter()
                    ->highlight(
                        VarDumperInterface::VARIABLE,
                        '$' . $this->varDumper->formatter()->filterEncodedChars($name)
                    ), ''
            ])
        );
        (new VarDumper(
            $this->varDumper->writer(),
            $this->varDumper->formatter(),
            new VarDumpable($var)
        ))
            ->withDepth(
                is_scalar($var)
                ? $this->depth - 1
                : $this->depth
            )
            ->withIndent($this->varDumper->indent())
            ->withKnownObjects($this->knownObjects)
            ->withProcessor();
    }

    private function handleNormalizeClassName(): void
    {
        if ((new StrBool($this->className))->startsWith(VarDumperInterface::CLASS_ANON) === true) {
            $this->className = VarDumperInterface::CLASS_ANON;
        }
    }
}
