<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\ExceptionHandler\src;

use const Chevere\CLI;

use function GuzzleHttp\Psr7\stream_for;

use Chevere\Console\Console;
use Chevere\ExceptionHandler\ExceptionHandler;
use Chevere\Http\Response;
use Chevere\Json;
use Chevere\Message\Message;
use Psr\Http\Message\StreamInterface;

/**
 * Provides ExceptionHandler output by passing a Formatter. FIXME: Don't handle responses!
 */
final class Output
{
    /** @var string The console|html content representation */
    private $content;

    /** @var string The text/plain content representation */
    private $textPlain;

    /** @var array */
    private $templateTags;

    /** @var ExceptionHandler */
    private $exceptionHandler;

    /** @var Formatter */
    private $formatter;

    /** @var StreamInterface */
    private $output;

    /** @var array */
    private $headers = [];

    /** @var string The rich template string. Note: Placeholders won't be visible when dumping to console */
    private $richTemplate;

    /** @var string The plain template string. */
    private $plainTemplate;

    public function __construct(ExceptionHandler $exceptionHandler, Formatter $formatter)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->formatter = $formatter;
        $this->generateTemplates();
        $this->parseTemplates();
        if ($exceptionHandler->request()->isXmlHttpRequest()) {
            $this->setJsonOutput();
        } else {
            if (CLI) {
                $this->setConsoleOutput();
            } else {
                $this->setHtmlOutput();
            }
        }
    }

    public function textPlain(): string
    {
        return $this->textPlain;
    }

    public function out(): void
    {
        if (CLI) {
            die(1);
        }

        $response = new Response();
        if ($this->exceptionHandler->request()->isXmlHttpRequest()) { } else {
            // $response = new HttpResponse();
        }
        $guzzle = $response->guzzle()
            ->withBody($this->output)
            ->withStatus(500);
        // $response->setLastModified(new DateTime());
        // foreach ($this->headers as $k => $v) {
        //     $response->headers->set($k, $v);
        // }
        $response->setGuzzle($guzzle);
        $response->sendBody();
    }

    private function parseTemplates(): void
    {
        $this->templateTags = $this->formatter->getTemplateTags();
        $this->content = strtr($this->richTemplate, $this->templateTags);
        $this->addTemplateTag('content', $this->content);
        $this->textPlain = strtr($this->plainTemplate, $this->templateTags);
    }

    /**
     * $table stores the template placeholders and its value.
     *
     * @param string $tagName Template tag name
     * @param mixed  $value   value
     */
    private function addTemplateTag(string $tagName, $value): void
    {
        $this->templateTags["%$tagName%"] = $value;
    }

    /**
     * @param string $tagName Template tag name
     */
    private function getTemplateTag(string $tagName): string
    {
        return $this->templateTags["%$tagName%"];
    }

    // FIXME: JsonApi Document
    private function setJsonOutput(): void
    {
        $json = new Json();
        $this->headers = array_merge($this->headers, Json::CONTENT_TYPE);
        $response = [Template::NO_DEBUG_TITLE_PLAIN, 500];
        $log = [
            'id' => $this->getTemplateTag('id'),
            'level' => $this->formatter->dataKey('loggerLevel'),
            'filename' => $this->getTemplateTag('logFilename'),
        ];
        switch ($this->exceptionHandler->isDebugEnabled()) {
            case 0:
                unset($log['filename']);
                break;
            case 1:
                $response[0] = $this->formatter->dataKey('thrown') . ' in ' . $this->getTemplateTag('file') . ':' . $this->getTemplateTag('line');
                $error = [];
                foreach (['file', 'line', 'code', 'message', 'class'] as $v) {
                    $error[$v] = $this->getTemplateTag($v);
                }
                $json->data->setKey('error', $error);
                break;
        }
        $json->data->setKey('log', $log);
        $json->setResponse(...$response);
        $this->output = (string) $json;
    }

    private function setHtmlOutput(): void
    {
        if ($this->exceptionHandler->isDebugEnabled()) {
            $bodyTemplate = Template::DEBUG_BODY_HTML;
        } else {
            $this->content = Template::NO_DEBUG_CONTENT_HTML;
            $this->addTemplateTag('content', $this->content);
            $this->addTemplateTag('title', Template::NO_DEBUG_TITLE_PLAIN);
            $bodyTemplate = Template::NO_DEBUG_BODY_HTML;
        }
        $this->addTemplateTag('body', strtr($bodyTemplate, $this->templateTags));
        $this->output = stream_for(strtr(Template::HTML_TEMPLATE, $this->templateTags));
    }

    private function setConsoleOutput(): void
    {
        foreach ($this->formatter->consoleSections() as $k => $v) {
            if ('title' == $k) {
                $tpl = $v[0];
            } else {
                Console::style()->section(strtr($v[0], $this->templateTags));
                $tpl = $v[1];
            }
            $message = strtr($tpl, $this->templateTags);
            if ('title' == $k) {
                Console::style()->error($message);
            } else {
                $message = (new Message($message))->toCliString();
                Console::style()->writeln($message);
            }
        }
        Console::style()->writeln('');
    }

    private function generateTemplates(): void
    {
        $templateStrings = new TemplatedStrings($this->formatter);
        $this->richTemplate = $templateStrings->rich();
        $this->plainTemplate = $templateStrings->plain();
    }
}