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

namespace Chevere\Tests\Routing\_resources\controllers;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Controller\ControllerParameter;
use Chevere\Components\Controller\ControllerParameters;
use Chevere\Components\Controller\ControllerResponse;
use Chevere\Interfaces\Controller\ControllerArgumentsInterface;
use Chevere\Interfaces\Controller\ControllerParametersInterface;
use Chevere\Interfaces\Controller\ControllerResponseInterface;
use Chevere\Components\Regex\Regex;

final class GetArticleController extends Controller
{
    public function getParameters(): ControllerParametersInterface
    {
        return (new ControllerParameters)
            ->withParameter(
                new ControllerParameter('id', new Regex('/^d+$/'))
            );
    }

    public function run(ControllerArgumentsInterface $arguments): ControllerResponseInterface
    {
        return new ControllerResponse(true);
    }
}
