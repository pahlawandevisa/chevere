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

namespace Chevere\Components\ThrowableHandler;

use Chevere\Components\ThrowableHandler\Documents\ConsoleDocument;
use Chevere\Components\ThrowableHandler\Documents\HtmlDocument;
use Chevere\Components\ThrowableHandler\Documents\PlainDocument;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\ErrorException;
use Chevere\Interfaces\ThrowableHandler\DocumentInterface;
use Chevere\Interfaces\ThrowableHandler\ThrowableHandlerInterface;
use Exception;
use function Chevere\Components\Writer\writers;

/**
 * @codeCoverageIgnore
 */
final class Handle
{
    public static function errorsAsExceptions(int $severity, string $message, string $file, int $line): void
    {
        throw new ErrorException(new Message($message), 0, $severity, $file, $line);
    }

    public static function plain(Exception $exception): void
    {
        self::write(new PlainDocument(self::getHandler($exception)));
    }

    public static function console(Exception $exception): void
    {
        self::write(new ConsoleDocument(self::getHandler($exception)));

        die(255);
    }

    public static function html(Exception $exception): void
    {
        self::write(new HtmlDocument(self::getHandler($exception)));
    }

    private static function write(DocumentInterface $document): void
    {
        writers()->out()->write($document->toString() . "\n");
    }

    private static function getHandler(Exception $exception): ThrowableHandlerInterface
    {
        return new ThrowableHandler(new ThrowableRead($exception));
    }
}
