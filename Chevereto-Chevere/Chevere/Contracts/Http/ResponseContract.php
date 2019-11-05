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

namespace Chevere\Contracts\Http;

use Chevere\Components\Http\GuzzleResponse;

interface ResponseContract
{
    /**
     * Creates a new instance with a default GuzzleResponse object.
     */
    public function __construct();

    /**
     * Return an instance with the specified GuzzleResponse.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified GuzzleResponse.
     */
    public function withGuzzle(GuzzleResponse $guzzle): ResponseContract;

    /**
     * Provides access to the GuzzleResponse instance.
     */
    public function guzzle(): GuzzleResponse;

    /**
     * Returns a single line representation of the HTTP response status.
     *
     * @return string The HTTP response status like: HTTP/1.1 200 OK
     */
    public function statusLine(): string;

    /**
     * Returns a the HTTP response headers, line-by-line.
     */
    public function headersString(): string;

    /**
     * Returns the HTTP response body.
     */
    public function content(): string;

    /**
     * Send the HTTP response headers.
     */
    public function sendHeaders(): ResponseContract;

    /**
     * Send the HTTP response body.
     */
    public function sendBody(): ResponseContract;
}
