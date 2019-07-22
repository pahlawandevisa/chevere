<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Console;

use RuntimeException;
use Monolog\Logger;
use Symfony\Component\Console\Application as ConsoleClient;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Chevere\App\App;

/**
 * Provides static access to the Chevere application console.
 */
class Console
{
    const VERBOSITY_QUIET = ConsoleOutput::VERBOSITY_QUIET;
    const VERBOSITY_NORMAL = ConsoleOutput::VERBOSITY_NORMAL;
    const VERBOSITY_VERBOSE = ConsoleOutput::VERBOSITY_VERBOSE;
    const VERBOSITY_VERY_VERBOSE = ConsoleOutput::VERBOSITY_VERY_VERBOSE;
    const VERBOSITY_DEBUG = ConsoleOutput::VERBOSITY_DEBUG;

    const OUTPUT_NORMAL = ConsoleOutput::OUTPUT_NORMAL;
    const OUTPUT_RAW = ConsoleOutput::OUTPUT_RAW;
    const OUTPUT_PLAIN = ConsoleOutput::OUTPUT_PLAIN;

    protected static $app;
    protected static $cli;
    protected static $available;

    /**
     * Init the Console facade.
     */
    public static function init()
    {
        $cli = new Cli(new ArgvInput());
        static::$cli = $cli;
        static::$available = true;
    }

    /**
     * Binds the App which interacts with this Console.
     *
     * @param App $app Chevere Application
     *
     * @return bool TRUE if Console binds to an App
     */
    public static function bind(App $app): bool
    {
        if (php_sapi_name() == 'cli') {
            static::$app = $app;

            return true;
        }

        return false;
    }

    /**
     * Get the value of cli.
     */
    public static function cli(): Cli
    {
        return static::$cli;
    }

    /**
     * Run the console command (if any).
     */
    public static function run()
    {
        $exitCode = static::$cli->runner();
        $command = static::$cli->command;
        if (null === $command) {
            exit($exitCode);
        }
        if (method_exists($command, 'callback')) {
            if (static::$app == null) {
                throw new RuntimeException('No app instance is defined.');
            }
            $exitCode = $command->callback(static::$app);
        }
        exit($exitCode);
    }

    /**
     * Get input string.
     */
    public static function inputString(): string
    {
        if (method_exists(static::$cli->input, '__toString')) {
            return static::$cli->input->__toString();
        }

        return '';
    }

    /**
     * Detects if Console is available.
     */
    public static function isRunning(): bool
    {
        return (bool) static::$available;
    }

    /**
     * Write messages to the console.
     *
     * @param string|array $messages the message as an iterable of strings or a single string
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public static function write($messages, int $options = self::OUTPUT_NORMAL): void
    {
        if (!static::isRunning()) {
            return;
        }
        static::io()->write($messages, false, $options);
    }

    /**
     * Write messages (new lines) to the console.
     *
     * @param string|array $messages the message as an iterable of strings or a single string
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public static function writeln($messages, int $options = self::OUTPUT_NORMAL): void
    {
        if (!static::isRunning()) {
            return;
        }
        static::io()->writeln($messages, $options);
    }

    public static function log($messages)
    {
        if (!static::isRunning()) {
            return;
        }
        static::io()->writeln($messages);
    }
}
