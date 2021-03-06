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

namespace Chevere;

use Chevere\Components\Bootstrap\Bootstrap;
use Chevere\Components\Filesystem\DirFromString;
use Chevere\Components\Instances\BootstrapInstance;

require 'vendor/autoload.php';

$dir = new DirFromString(__DIR__ . '/');

new BootstrapInstance(
    (new Bootstrap($dir))
);
