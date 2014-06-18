<?php
/**
 * This file is part of the html-validator package.
 *
 * (c) Espen Hovlandsdal <espen@hovlandsdal.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlValidator;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\RequestException;
use HtmlValidator\Exception\ServerException;
use HtmlValidator\Exception\UnknownParserException;

/**
 * HTML Validator (uses Validator.nu as backend)
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) Espen Hovlandsdal
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/rexxars/html-validator
 */
class Validator {

    /**
     * Parser constants
     *
     * @var string
     */
    const PARSER_XML     = 'xml';
    const PARSER_XMLDTD  = 'xmldtd';
    const PARSER_HTML    = 'html';
    const PARSER_HTML5   = 'html5';
    const PARSER_HTML4   = 'html4';
    const PARSER_HTML4TR = 'html4tr';

    /**
     * Characters sets
     *
     * @var string
     */
    const CHARSET_UTF8 = 'utf-8';

    /**
     * Default validator URL
     *
     * @var string
     */
    const DEFAULT_VALIDATOR_URL = 'http://validator.nu';

    /**
     * Holds the HTTP client used to communicate with the API
     * 
     * @var Guzzle\Http\Client
     */
    private $httpClient;

    /**
     * Parser to use for validating
     * 
     * @var string
     */
    private $parser;

    /**
     * Default charset to report to the validator
     * 
     * @var string
     */
    private $defaultCharset = self::CHARSET_UTF8;

    /**
     * Node wrapper tool
     * 
     * @var HtmlValidator\NodeWrapper
     */
    private $nodeWrapper;

    /**
     * Constructs a new validator instance
     */
    public function __construct($validatorUrl = self::DEFAULT_VALIDATOR_URL, $parser = self::PARSER_HTML5) {
        $this->httpClient = new HttpClient($validatorUrl);
        $this->httpClient->setDefaultOption('headers/User-Agent', 'rexxars/html-validator');

        $this->nodeWrapper = new NodeWrapper();

        $this->setParser($parser);
    }

    /**
     * Set parser to use for the given markup
     * 
     * @param string $parser Parser name (use Validator::PARSER_*)
     * @return Validator Returns current instance
     * @throws UnknownParserException If parser specified is not known
     */
    public function setParser($parser) {
        switch ($parser) {
            case self::PARSER_XML:
            case self::PARSER_XMLDTD:
            case self::PARSER_HTML:
            case self::PARSER_HTML5:
            case self::PARSER_HTML4:
            case self::PARSER_HTML4TR:
                $this->parser = $parser;
                return $this;
            default:
                throw new UnknownParserException('Unknown parser "' . $parser . '"');
        }
    }

    /**
     * Set the default charset to report to the validator
     * 
     * @param string $charset Charset name (defaults to 'utf-8')
     */
    public function setDefaultCharset($charset) {
        $this->defaultCharset = $charset;

        return $this;
    }

    /**
     * Get the correct mime-type for the given parser
     * 
     * @param  string $parser Parser name
     * @return string
     */
    private function getMimeTypeForParser($parser) {
        switch ($parser) {
            case self::PARSER_XML:
                return 'application/xml';
            case self::PARSER_XMLDTD:
                return 'application/xml-dtd';
            case self::PARSER_HTML:
            case self::PARSER_HTML5:
            case self::PARSER_HTML4:
            case self::PARSER_HTML4TR:
                return 'text/html';
        }
    }

    /**
     * Get a string usable for the Content-Type header,
     * based on the given mime type and charset
     * 
     * @param  string $mimeType Mime type to use
     * @param  string $charset  Character set to use
     * @return string
     */
    private function getContentTypeString($mimeType, $charset) {
        return $mimeType . '; charset=' . $charset;
    }

    /**
     * Validate a complete document (including DOCTYPE)
     * 
     * @param  string $document HTML/XML-document, as string
     * @param  string $charset  Charset to report (defaults to utf-8)
     * @return HtmlValidator\Response
     */
    public function validateDocument($document, $charset = null) {
        $document = (string) $document;
        $charset  = $charset ?: $this->defaultCharset;
        $headers  = array(
            'Content-Type'   => $this->getContentTypeString(
                $this->getMimeTypeForParser($this->parser),
                $charset
            ),
            'Content-Length' => strlen($document),
        );

        $request = $this->httpClient->post('', $headers, $document, array(
            'query' => array(
                'out'    => 'json',
                'parser' => $this->parser,
            ),
        ));

        try {
            $response = new Response($request->send());
        } catch (RequestException $e) {
            throw new ServerException($e->getMessage());
        }

        return $response;
    }

    /**
     * Validates a chunk of HTML/XML. A surrounding document will be
     * created on the fly based on the formatter specified. Note that
     * this can lead to unexpected behaviour:
     *   - Line numbers reported will be incorrect
     *   - Injected document might not be right for your use case
     *   
     * NOTE: Use validateDocument() whenever possible.
     * 
     * @param  string $nodes   HTML/XML-chunk, as string
     * @param  string $charset Charset to report (defaults to utf-8)
     * @return HtmlValidator\Response
     */
    public function validateNodes($nodes, $charset = null) {
        $wrapped = $this->nodeWrapper->wrap($this->parser, $nodes, $charset);

        return $this->validateDocument($wrapped, $charset);
    }

    /**
     * Validate a complete document (including DOCTYPE)
     * 
     * @param  string $document HTML/XML-document, as string
     * @param  string $charset  Charset to report (defaults to utf-8)
     * @return HtmlValidator\Response
     */
    public function validate($document, $charset = null) {
        return $this->validateDocument($document, $charset);
    }

}