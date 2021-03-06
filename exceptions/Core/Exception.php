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

namespace Chevere\Exceptions\Core;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\Traits\ExceptionTrait;
use Chevere\Interfaces\Message\MessageInterface;
use Throwable;

/**
 * Extends \Exception with Message support.
 */
class Exception extends \Exception
{
    use ExceptionTrait;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(MessageInterface $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->_message = $message ?? new Message('');
        parent::__construct($this->_message->toString(), $code, $previous);
    }
}
