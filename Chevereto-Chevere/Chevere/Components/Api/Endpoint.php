<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Api;

use Chevere\Components\Controllers\Api\HeadController;
use Chevere\Components\Controllers\Api\OptionsController;
use Chevere\Components\Http\Method;
use Chevere\Components\Http\MethodController;
use Chevere\Contracts\Api\src\EndpointContract;
use Chevere\Contracts\Http\MethodControllerCollectionContract;

final class Endpoint implements EndpointContract
{
    /** @var array */
    private $array;

    /** @var MethodControllerCollectionContract */
    private $methodControllerCollection;

    /**
     * {@inheritdoc}
     */
    public function __construct(MethodControllerCollectionContract $collection)
    {
        $this->array = [];
        $this->methodControllerCollection = $collection;
        $this->fillEndpointOptions();
        $this->autofillMissingOptionsHead();
    }

    /**
     * {@inheritdoc}
     */
    public function methodControllerCollection(): MethodControllerCollectionContract
    {
        return $this->methodControllerCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->array;
    }

    private function fillEndpointOptions(): void
    {
        foreach ($this->methodControllerCollection as $method) {
            $httpMethod = $method->method();
            $controllerClassName = $method->controllerName();
            $httpMethodOptions = [];
            $httpMethodOptions['description'] = $controllerClassName::description();
            $controllerParameters = $controllerClassName::parameters();
            if (isset($controllerParameters)) {
                $httpMethodOptions['parameters'] = $controllerParameters;
            }
            $this->array['OPTIONS'][$httpMethod] = $httpMethodOptions;
        }
    }

    private function autofillMissingOptionsHead(): void
    {
        foreach ([
            'OPTIONS' => [
                OptionsController::class, [
                    'description' => OptionsController::description(),
                ],
            ],
            'HEAD' => [
                HeadController::class, [
                    'description' => HeadController::description(),
                ],
            ],
        ] as $k => $v) {
            if (!$this->methodControllerCollection->has(new Method($k))) {
                $this->methodControllerCollection = $this->methodControllerCollection
                    ->withAddedMethodController(
                        new MethodController(new Method($k), $v[0])
                    );
                $this->array['OPTIONS'][$k] = $v[1];
            }
        }
    }
}
