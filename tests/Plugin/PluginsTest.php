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

namespace Chevere\Tests\Plugin;

use Chevere\Components\ClassMap\ClassMap;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Plugin\Plugins;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Exceptions\Plugin\PluggableNotRegisteredException;
use Chevere\Exceptions\Plugin\PlugsFileNotExistsException;
use Chevere\Exceptions\Plugin\PlugsQueueInterfaceException;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Plugin\PlugsQueueInterface;
use PHPUnit\Framework\TestCase;

final class PluginsTest extends TestCase
{
    private PathInterface $resourcesPath;

    public function setUp(): void
    {
        $this->resourcesPath = new Path(
            (__DIR__) . '/_resources/PluginsTest/'
        );
    }

    public function testEmpty(): void
    {
        $classMap = new ClassMap;
        $plugins = new Plugins($classMap);
        $this->assertNotSame($classMap, $plugins->classMap());
        $this->assertEquals($classMap, $plugins->classMap());
        $pluggable = 'notRegistered';
        $this->expectException(PluggableNotRegisteredException::class);
        $plugins->getPlugsQueue($pluggable);
    }

    public function testRegisteredNotExists(): void
    {
        $pluggable = 'registered';
        $map = uniqid() . '.php';
        $plugins = new Plugins(
            (new ClassMap)
                ->withStrict(false)
                ->withPut($pluggable, $map)
        );
        $this->expectException(PlugsFileNotExistsException::class);
        $plugins->getPlugsQueue($pluggable);
    }

    public function testRegisteredWrongReturnType(): void
    {
        $pluggable = 'registered';
        $map = $this->resourcesPath->getChild('invalid.php')->absolute();
        $plugins = new Plugins(
            (new ClassMap)
                ->withStrict(false)
                ->withPut($pluggable, $map)
        );
        $this->expectException(PlugsQueueInterfaceException::class);
        $plugins->getPlugsQueue($pluggable);
    }

    public function testRegisteredCorrupted(): void
    {
        $pluggable = 'registered';
        $map = $this->resourcesPath->getChild('corrupted.php')->absolute();
        $plugins = new Plugins(
            (new ClassMap)
                ->withStrict(false)
                ->withPut($pluggable, $map)
        );
        $this->expectException(RuntimeException::class);
        $plugins->getPlugsQueue($pluggable);
    }

    public function testRegisteredHooks(): void
    {
        $pluggable = 'registered';
        $map = $this->resourcesPath->getChild('hooks.php')->absolute();
        $plugins = new Plugins(
            (new ClassMap)
                ->withStrict(false)
                ->withPut($pluggable, $map)
        );
        $this->assertInstanceOf(
            PlugsQueueInterface::class,
            $plugins->getPlugsQueue($pluggable)
        );
    }
}
