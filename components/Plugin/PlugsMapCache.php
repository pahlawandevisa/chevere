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

namespace Chevere\Components\Plugin;

use Chevere\Components\Cache\CacheKey;
use Chevere\Components\ClassMap\ClassMap;
use Chevere\Components\Filesystem\FilePhpReturnFromString;
use Chevere\Components\Message\Message;
use Chevere\Components\Str\Str;
use Chevere\Components\VarExportable\VarExportable;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Cache\CacheInterface;
use Chevere\Interfaces\Cache\CacheKeyInterface;
use Chevere\Interfaces\ClassMap\ClassMapInterface;
use Chevere\Interfaces\Plugin\PlugsMapCacheInterface;
use Chevere\Interfaces\Plugin\PlugsMapInterface;
use Chevere\Interfaces\Plugin\PlugsQueueTypedInterface;
use ReflectionClass;
use Throwable;
use TypeError;

final class PlugsMapCache implements PlugsMapCacheInterface
{
    /**
     * @var ClassMap [pluggableClassName => path_to_plugsQueue,]
     */
    private ClassMap $classMap;

    private CacheInterface $cache;

    private CacheKeyInterface $classMapKey;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->classMapKey = new CacheKey(self::KEY_CLASS_MAP);
    }

    public function withPut(PlugsMapInterface $plugsMap): PlugsMapCacheInterface
    {
        $new = clone $this;
        $new->classMap = new ClassMap;
        foreach ($plugsMap->getGenerator() as $pluggableName => $plugsQueueTyped) {
            $classNameAsPath = (new Str($pluggableName))->forwardSlashes()->toString();
            $cacheAt = $new->cache->getChild($classNameAsPath . '/');
            $queueName = (new ReflectionClass($plugsQueueTyped))->getShortName();
            $cacheAt = $cacheAt->withPut(new CacheKey($queueName), new VarExportable($plugsQueueTyped));
            $new->classMap = $new->classMap
                ->withPut(
                    $pluggableName,
                    $cacheAt->puts()[$queueName]['path']
                );
        }
        $new->cache = $new->cache
            ->withPut(
                $new->classMapKey,
                new VarExportable($new->classMap)
            );

        return $new;
    }

    public function hasPlugsQueueFor(string $className): bool
    {
        if (!$this->cache->exists($this->classMapKey)) {
            return false;
        }
        try {
            return $this->getClassMap()->has($className);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @throws DirUnableToCreateException
     */
    public function getPlugsQueueFor(string $className): PlugsQueueTypedInterface
    {
        if (!$this->cache->exists($this->classMapKey)) {
            throw new OutOfBoundsException(
                (new Message('No cache exists at %key% cache key'))
                    ->code('%key%', $this->classMapKey->toString()),
                1
            );
        }
        try {
            $classMap = $this->getClassMap();
        } catch (Throwable $e) {
            throw new OutOfBoundsException(
                ($e instanceof Exception
                    ? $e->message()
                    : new Message($e->getMessage())),
                2
            );
        }
        if (!$classMap->has($className)) {
            throw new OutOfBoundsException(
                (new Message('Class name %className% is not mapped'))
                    ->code('%className%', $className),
                3
            );
        }

        try {
            $path = $classMap->get($className);

            return (new FilePhpReturnFromString($path))->withStrict(false)->var();
        } catch (Exception $e) {
            throw new OutOfBoundsException(
                ($e instanceof Exception
                    ? $e->message()
                    : new Message($e->getMessage())),
                4
            );
        }
    }

    private function getClassMap(): ClassMapInterface
    {
        return $this->cache->get($this->classMapKey)->var();
    }
}
