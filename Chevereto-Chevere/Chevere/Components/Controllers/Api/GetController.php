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

namespace Chevere\Components\Controllers\Api;

use InvalidArgumentException;

use Chevere\Components\Api\Api;
use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;

use function console;

use const Chevere\CLI;

// TODO: Use json:api immutable

/**
 * Exposes an API endpoint.
 */
final class GetController extends Controller
{
    protected static $description = 'Retrieve endpoint.';

    /** @var string */
    private $endpoint;

    /**
     * @param string $endpoint an API endpoint (/api)
     */
    public function __invoke(?string $endpoint = null)
    {
        if (isset($endpoint)) {
            $route = $this->app()->router()->resolve($endpoint);
        } else {
            $route = $this->app()->route();
            if (isset($route)) {
                $endpoint = $route->path();
            } else {
                $msg = 'Must provide the %s argument when running this callable without route context.';
                $message = (new Message($msg))->code('%s', '$endpoint')->toString();
                if (CLI) {
                    console()->style()->error($message);

                    return;
                }
                throw new InvalidArgumentException($message);
            }
        }

        if (!isset($route)) {
            $response = $this->app()->response();
            $response->setStatusCode(404);

            return;
        }

        $this->endpoint = $endpoint;

        $this->process();
    }

    private function process()
    {
        $endpointData = Api::endpoint($this->endpoint);
        // dd($this->endpoint, $endpointData);
        if ($endpointData) {
            $response = $this->app()->response();
            $response->setMeta(['api' => $this->endpoint]);
            // foreach ($endpointData as $property => $data) {
            //     if ($property == 'wildcards') {
            //         continue;
            //     }
            //     $data = new Data($property, 'endpoint');
            //     $data->addAttribute('entry', $data);
            //     $response->addData($data);
            // }
        }
        $response->setStatusCode(200);
    }
}