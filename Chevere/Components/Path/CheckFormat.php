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

namespace Chevere\Components\Path;

use Chevere\Components\Message\Message;
use Chevere\Components\Path\Exceptions\PathDotSlashException;
use Chevere\Components\Path\Exceptions\PathDoubleDotsDashException;
use Chevere\Components\Path\Exceptions\PathExtraSlashesException;
use Chevere\Components\Path\Exceptions\PathInvalidException;
use Chevere\Components\Path\Exceptions\PathNotAbsoluteException;
use Chevere\Components\Path\Interfaces\CheckFormatInterface;
use function ChevereFn\stringStartsWith;

final class CheckFormat implements CheckFormatInterface
{
    private string $path;

    /**
     * @throws PathInvalidException if the $path format is invalid
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->assertAbsolutePath();
        $this->assertNoDoubleDots();
        $this->assertNoDots();
        $this->assertNoExtraSlashes();
    }

    private function assertAbsolutePath(): void
    {
        if (!stringStartsWith('/', $this->path)) {
            throw new PathNotAbsoluteException(
                (new Message('Path %path% must start with %char%'))
                    ->code('%path%', $this->path)
                    ->code('%char%', '/')
                    ->toString()
            );
        }
    }

    private function assertNoDoubleDots(): void
    {
        if (false !== strpos($this->path, '../')) {
            throw new PathDoubleDotsDashException(
                (new Message('Must omit %chars% for path %path%'))
                    ->code('%chars%', '../')
                    ->code('%path%', $this->path)
                    ->toString()
            );
        }
    }

    private function assertNoDots(): void
    {
        if (false !== strpos($this->path, './')) {
            throw new PathDotSlashException(
                (new Message('Must omit %chars% for path %path%'))
                    ->code('%chars%', './')
                    ->code('%path%', $this->path)
                    ->toString()
            );
        }
    }

    private function assertNoExtraSlashes(): void
    {
        if (false !== strpos($this->path, '//')) {
            throw new PathExtraSlashesException(
                (new Message('Path %path% contains extra-slashes'))
                    ->code('%path%', $this->path)
                    ->toString()
            );
        }
    }
}
