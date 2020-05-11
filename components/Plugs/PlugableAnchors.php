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

namespace Chevere\Components\Plugs;

use Chevere\Components\Message\Message;
use Ds\Set;
use LogicException;
use function DeepCopy\deep_copy;

final class PlugableAnchors
{
    private Set $set;

    public function __construct()
    {
        $this->set = new Set;
    }

    public function withAdded(string $anchor): PlugableAnchors
    {
        $new = clone $this;
        if ($new->has($anchor)) {
            throw new LogicException(
                (new Message('Anchor %anchor% has been already added'))
                    ->code('%anchor%', $anchor)
            );
        }
        $new->set->add($anchor);

        return $new;
    }

    public function has(string $anchor): bool
    {
        return $this->set->contains($anchor);
    }

    public function set(): Set
    {
        return deep_copy($this->set);
    }
}