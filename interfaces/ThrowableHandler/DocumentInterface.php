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

namespace Chevere\Interfaces\ThrowableHandler;

use Symfony\Component\Console\Output\OutputInterface;

interface DocumentInterface
{
    const SECTION_TITLE = 'title';
    const SECTION_MESSAGE = 'message';
    const SECTION_ID = 'id';
    const SECTION_TIME = 'time';
    const SECTION_STACK = 'stack';
    const SECTION_SERVER = 'server';

    const TAG_TITLE = '%title%';
    const TAG_MESSAGE = '%message%';
    const TAG_CODE_WRAP = '%codeWrap%';
    const TAG_ID = '%id%';
    const TAG_FILE_LINE = '%fileLine%';
    const TAG_DATE_TIME_UTC_ATOM = '%dateTimeUtcAtom%';
    const TAG_TIMESTAMP = '%timestamp%';
    const TAG_STACK = '%stack%';
    const TAG_PHP_UNAME = '%phpUname%';

    const SECTIONS = [
        self::SECTION_TITLE,
        self::SECTION_MESSAGE,
        self::SECTION_ID,
        self::SECTION_TIME,
        self::SECTION_STACK,
        self::SECTION_SERVER,
    ];

    const SECTIONS_VERBOSITY = [
        self::SECTION_TITLE => OutputInterface::VERBOSITY_QUIET,
        self::SECTION_MESSAGE => OutputInterface::VERBOSITY_QUIET,
        self::SECTION_ID => OutputInterface::VERBOSITY_QUIET,
        self::SECTION_TIME => OutputInterface::VERBOSITY_VERBOSE,
        self::SECTION_STACK => OutputInterface::VERBOSITY_VERY_VERBOSE,
        self::SECTION_SERVER => OutputInterface::VERBOSITY_VERBOSE,
    ];

    public function __construct(ThrowableHandlerInterface $exceptionHandler);

    /**
     * Return an instance with the specified verbosity.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified verbosity.
     *
     * Calling this method will reset the document sections to fit the target verbosity.
     */
    public function withVerbosity(int $verbosity): DocumentInterface;

    /**
     * Provides access to the instance verbosity.
     */
    public function verbosity(): int;

    /**
     * Returns the document title section.
     */
    public function getSectionTitle(): string;

    /**
     * Returns the document message section.
     */
    public function getSectionMessage(): string;

    /**
     * Returns the document id section.
     */
    public function getSectionId(): string;

    /**
     * Returns the document time section.
     */
    public function getSectionTime(): string;

    /**
     * Returns the document stack section.
     */
    public function getSectionStack(): string;

    /**
     * Returns the document server section.
     */
    public function getSectionServer(): string;

    public function toString(): string;

    public function getTemplate(): array;

    public function getFormatter(): FormatterInterface;
}
