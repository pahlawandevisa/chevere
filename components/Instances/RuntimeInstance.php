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

namespace Chevere\Components\Instances;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\LogicException;
use Chevere\Interfaces\Runtime\RuntimeInterface;

/**
 * @codeCoverageIgnore
 */
final class RuntimeInstance
{
    private static RuntimeInterface $instance;

    public function __construct(RuntimeInterface $runtime)
    {
        self::$instance = $runtime;
    }

    public static function get(): RuntimeInterface
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                (new Message('No runtime instance present'))
            );
        }

        return self::$instance;
    }
}
