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

namespace Chevereto\Chevere\ErrorHandler;

use Throwable;
use ErrorException;
use const Chevereto\Chevere\CORE_NS_HANDLE;
use Chevereto\Chevere\Console;
use Chevereto\Chevere\Path;
use Chevereto\Chevere\Utils\Dump;
use Chevereto\Chevere\Utils\DumpPlain;
use Chevereto\Chevere\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides formatting in HTML (default), plain text and console (colorized).
 */
class Formatter
{
    /** @var string Number of fixed columns for plaintext display */
    const COLUMNS = 120;

    /** ErrorHandler sections */
    const SECTION_TITLE = 'title';
    const SECTION_MESSAGE = 'message';
    const SECTION_ID = 'id';
    const SECTION_TIME = 'time';
    const SECTION_STACK = 'stack';
    const SECTION_CLIENT = 'client';
    const SECTION_REQUEST = 'request';
    const SECTION_SERVER = 'server';

    /** Verbose aware console sections. */
    const CONSOLE_TABLE = [
        self::SECTION_TITLE => OutputInterface::VERBOSITY_QUIET,
        self::SECTION_MESSAGE => OutputInterface::VERBOSITY_QUIET,
        self::SECTION_ID => OutputInterface::VERBOSITY_NORMAL,
        self::SECTION_TIME => OutputInterface::VERBOSITY_VERBOSE,
        self::SECTION_STACK => OutputInterface::VERBOSITY_VERY_VERBOSE,
        self::SECTION_CLIENT => OutputInterface::VERBOSITY_VERBOSE,
        self::SECTION_REQUEST => false,
        self::SECTION_SERVER => false,
    ];

    /** @var ErrorHandler */
    public $errorHandler;

    /** @var string */
    public $css;

    /** @var string */
    public $lineBreak;

    /** @var string IP address */
    public $clientIp;

    /** @var string User-Agent */
    public $clientUserAgent;

    /** @var string The request URI */
    public $url;

    /** @var string */
    public $httpRequestMethod;

    /** @var string */
    public $serverHost;

    /** @var int */
    public $serverPort;

    /** @var string */
    public $serverProtocol;

    /** @var string */
    public $serverSoftware;

    /** @var string */
    public $bodyClass;

    /** @var Throwable */
    protected $exception;

    /** @var string */
    public $className;

    /** @var string FIXME: Better name */
    public $thrown;

    // The actual stacks
    protected $plainStack;
    protected $richStack;
    protected $consoleStack;

    /** @var array */
    protected $plainContentSections;

    /** @var array */
    protected $richContentSections;

    public $consoleSections;

    protected $richContentTemplate;
    protected $plainContentTemplate;

    /** @var string The plain content representation */
    public $plainContent;

    // Exception properties FIXME: BETTER NAMES
    public $code;
    public $message;
    public $type;
    public $file;
    public $line;
    public $loggerLevel;

    public $content;

    public $title;

    public $table;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
        $this->processServerProperties();
        $this->proccessException();
        $this->processStack();
        $this->processContentSections();
        $this->processContentGlobals();
        $this->generateTemplates();
        $this->setContentProperties();
        $this->setBodyClass();
    }

    public function setLineBreak(string $lineBreak)
    {
        $this->lineBreak = $lineBreak;
    }

    public function setCss(string $css)
    {
        $this->css = $css;
    }

    protected function processServerProperties()
    {
        if ('cli' == php_sapi_name()) {
            $this->clientIp = $_SERVER['argv'][0];
            $this->clientUserAgent = Console::inputString();
        } else {
            if ($httpRequest = $this->errorHandler->httpRequest) {
                $this->uri = $httpRequest->readInfoKey('requestUri') ?? 'unknown';
                $this->clientUserAgent = $httpRequest->getHeaders()->get('User-Agent');
                $this->httpRequestMethod = $httpRequest->readInfoKey('method');
                $this->serverHost = $httpRequest->readInfoKey('host');
                $this->serverPort = (int) $httpRequest->readInfoKey('port');
                $this->serverProtocol = $httpRequest->readInfoKey('protocolVersion');
                $this->serverSoftware = $httpRequest->getServer()->get('SERVER_SOFTWARE');
                $this->clientIp = $httpRequest->readInfoKey('clientIp');
            }
        }
    }

    protected function setBodyClass()
    {
        $this->bodyClass = !headers_sent() ? 'body--flex' : 'body--block';
    }

    protected function proccessException()
    {
        $this->exception = $this->errorHandler->arguments[0];
        $this->className = get_class($this->exception);
        if (Str::startsWith(CORE_NS_HANDLE, $this->className)) {
            $this->className = Str::replaceFirst(CORE_NS_HANDLE, null, $this->className);
        }
        $this->thrown = $this->className . ' thrown';
        if ($this->exception instanceof ErrorException) {
            $code = $this->exception->getSeverity();
            $e_type = $code;
        } else {
            $code = $this->exception->getCode();
            $e_type = E_ERROR;
        }
        $this->code = $code;
        $this->type = $this->errorHandler::getErrorByCode($e_type);
        $this->loggerLevel = $this->errorHandler::getLoggerLevel($e_type) ?? 'error'; // FIXME: logger level is referenced everywhere
        $this->message = $this->exception->getMessage();
        $this->file = Path::normalize($this->exception->getFile());
        $this->line = (string) $this->exception->getLine();
    }

    protected function processStack()
    {
        $i = 0;
        $trace = $this->exception->getTrace();
        if ($this->exception instanceof ErrorException) {
            $this->thrown = $this->type;
            $this->message = $this->message;
            unset($trace[0]);
        }
        $stack = new Stack($trace);
        // FIXME: Case-aware stack filling, console is not needed in web
        // $this->consoleStack = $stack->getConsoleStack();
        $this->richStack = $stack->getRichStack();
        $this->plainStack = $stack->getPlainStack();
    }

    protected function processContentSections()
    {
        // Plain (txt) is the default "always do" format.
        $plain = [
            static::SECTION_TITLE => ['%title% <span>in&nbsp;%file%:%line%</span>'],
            static::SECTION_MESSAGE => ['# Message', '%message%' . ($this->code ? ' [Code #%code%]' : null)],
            static::SECTION_TIME => ['# Time', '%datetimeUtc% [%timestamp%]'],
        ];
        $plain[static::SECTION_ID] = ['# Incident ID:%id%', 'Logged at %logFilename%'];
        $plain[static::SECTION_STACK] = ['# Stack trace', '%plainStack%'];
        $plain[static::SECTION_CLIENT] = ['# Client', '%clientIp% %clientUserAgent%'];
        $plain[static::SECTION_REQUEST] = ['# Request', '%serverProtocol% %httpRequestMethod% %uri%'];
        $plain[static::SECTION_SERVER] = ['# Server', '%serverHost% (port:%serverPort%) %serverSoftware%'];

        if ('cli' == php_sapi_name()) {
            $verbosity = Console::output()->getVerbosity();
        }

        foreach ($plain as $k => $v) {
            $keyString = (string) $k;
            if ('cli' == php_sapi_name() && false == static::CONSOLE_TABLE[$k]) {
                continue;
            }
            $this->setPlainContentSection($keyString, $v);
            if (isset($verbosity)) {
                $lvl = static::CONSOLE_TABLE[$k];
                if (false === $lvl || $verbosity < $lvl) {
                    continue;
                }
                if ($k == static::SECTION_STACK) {
                    $v[1] = '%consoleStack%';
                }
                $this->setConsoleSection($keyString, $v);
            } else {
                if ($k == static::SECTION_STACK) {
                    $v[1] = '%richStack%';
                }
                $this->setRichContentSection($keyString, $v);
            }
        }
    }

    protected function processContentGlobals()
    {
        foreach (['GET', 'POST', 'FILES', 'COOKIE', 'SESSION', 'SERVER'] as $v) {
            $k = '_' . $v;
            $v = isset($GLOBALS[$k]) ? $GLOBALS[$k] : null;
            if ($v) {
                $this->setRichContentSection($k, ['$' . $k, $this->wrapStringHr('<pre>' . Dump::out($v) . '</pre>')]);
                $this->setPlainContentSection($k, ['$' . $k, strip_tags($this->wrapStringHr(DumpPlain::out($v)))]);
            }
        }
    }

    /**
     * @param string $key     content section key
     * @param array  $section section content [title, content]
     */
    protected function setPlainContentSection(string $key, array $section): void
    {
        $this->plainContentSections[$key] = $section;
    }

    /**
     * @param string $key     console section key
     * @param array  $section section content [title, <content>]
     */
    protected function setConsoleSection(string $key, array $section): void
    {
        $section = array_map(function (string $value) {
            return strip_tags(html_entity_decode($value));
        }, $section);
        $this->consoleSections[$key] = $section;
    }

    /**
     * @param string $key     content section key
     * @param array  $section section content [title, content]
     */
    protected function setRichContentSection(string $key, array $section): void
    {
        $section[0] = Str::replaceFirst('# ', '<span class="hide"># </span>', $section[0]);
        $this->richContentSections[$key] = $section;
    }

    protected function setContentProperties()
    {
        $this->title = $this->thrown;
        $this->message = nl2br($this->message);
    }

    public function parseTemplate()
    {
        $this->templateTags = [
            '%id%' => $this->errorHandler->id,
            '%datetimeUtc%' => $this->errorHandler->datetimeUtc,
            '%timestamp%' => $this->errorHandler->timestamp,
            '%loadedConfigFilesString%' => $this->errorHandler->loadedConfigFilesString,
            '%logFilename%' => $this->errorHandler->logFilename,
            '%css%' => $this->css,
            '%bodyClass%' => $this->bodyClass,
            '%body%' => null,
            '%title%' => $this->title,
            '%content%' => $this->content,
            '%title%' => $this->title,
            '%file%' => $this->file,
            '%line%' => $this->line,
            '%message%' => $this->message,
            '%code%' => $this->code,
            '%plainStack%' => $this->plainStack,
            '%consoleStack%' => $this->consoleStack,
            '%richStack%' => $this->richStack,
            '%clientIp%' => $this->clientIp,
            '%clientUserAgent%' => $this->clientUserAgent,
            '%serverProtocol%' => $this->serverProtocol,
            '%httpRequestMethod%' => $this->httpRequestMethod,
            '%uri%' => $this->uri ?? null,
            '%serverHost%' => $this->serverHost,
            '%serverPort%' => $this->serverPort,
            '%serverSoftware%' => $this->serverSoftware,
        ];
        $this->content = strtr($this->richContentTemplate, $this->templateTags);
        $this->plainContent = strtr($this->plainContentTemplate, $this->templateTags);
        $this->addTemplateTag('content', $this->content);
    }

    protected function generateTemplates()
    {
        $sections_length = count($this->plainContentSections);
        $i = 0;
        foreach ($this->plainContentSections as $k => $plainSection) {
            $richSection = $this->richContentSections[$k] ?? null;
            $section_length = count($plainSection);
            if (0 == $i || isset($plainSection[1])) {
                $this->richContentTemplate .= '<div class="t' . (0 == $i ? ' t--scream' : null) . '">' . $richSection[0] . '</div>';
                $this->plainContentTemplate .= html_entity_decode($plainSection[0]);
                if (0 == $i) {
                    $this->richContentTemplate .= "\n" . '<div class="hide">' . str_repeat('=', static::COLUMNS) . '</div>';
                    $this->plainContentTemplate .= "\n" . str_repeat('=', static::COLUMNS);
                }
            }
            if ($i > 0) {
                $j = 1 == $section_length ? 0 : 1;
                for ($j; $j < $section_length; ++$j) {
                    if ($section_length > 1) {
                        $this->richContentTemplate .= "\n";
                        $this->plainContentTemplate .= "\n";
                    }
                    $this->richContentTemplate .= '<div class="c">' . $richSection[$j] . '</div>';
                    $this->plainContentTemplate .= $plainSection[$j];
                }
            }
            if ($i + 1 < $sections_length) {
                $this->richContentTemplate .= "\n" . '<br>' . "\n";
                $this->plainContentTemplate .= "\n\n";
            }
            ++$i;
        }
    }

    /**
     * @param string $text text to wrap
     *
     * @return string wrapped text
     */
    protected function wrapStringHr(string $text): string
    {
        return $this->lineBreak . "\n" . $text . "\n" . $this->lineBreak;
    }

    /**
     * $table stores the template placeholders and its value.
     *
     * @param string $tagName Template tag name
     * @param mixed  $value   value
     */
    public function addTemplateTag(string $tagName, $value): void
    {
        $this->templateTags["%$tagName%"] = $value;
    }

    /**
     * @param string $tagName Template tag name
     */
    public function getTemplateTag(string $tagName)
    {
        return $this->templateTags["%$tagName%"] ?? null;
    }
}