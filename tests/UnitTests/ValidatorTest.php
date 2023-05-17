<?php
/**
 * This file is part of the html-validator package.
 *
 * (c) Espen Hovlandsdal <espen@hovlandsdal.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlValidator\Tests\UnitTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use HtmlValidator\Exception\ServerException;
use HtmlValidator\Exception\UnknownParserException;
use HtmlValidator\Validator;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class ValidatorTest extends TestCase {

    /**
     * Ensure the client can be instantiated without errors with no arguments passed
     *
     * @covers \HtmlValidator\Validator::__construct
     */
    public function testCanConstructClientWithDefaultArguments() {
        $client = new Validator();
        $this->assertInstanceOf(Validator::class, $client);
    }

    /**
     * Test that we can both set and get the parsers to be used when validating
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getParser
     * @covers \HtmlValidator\Validator::setParser
     * @throws UnknownParserException
     */
    public function testCanSetAndGetParsers() {
        $client = new Validator();

        // Ensure that we default to the 'HTML5' parser
        $this->assertSame(Validator::PARSER_HTML5, $client->getParser());

        // Ensure we can set a different parser (and that the setter returns the client)
        $this->assertSame($client, $client->setParser(Validator::PARSER_XML));

        // Ensure that the set value is the one returned from the getter
        $this->assertSame(Validator::PARSER_XML, $client->getParser());
    }

    /**
     * Test that we can both set and get the charset to be used when validating
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getCharset
     * @covers \HtmlValidator\Validator::setCharset
     */
    public function testCanSetAndGetCharset() {
        $client = new Validator();

        // Ensure default is UTF-8
        $this->assertSame(Validator::CHARSET_UTF_8, $client->getCharset());

        // Ensure we can set a different charset (and that the setter returns the client)
        $this->assertSame($client, $client->setCharset(Validator::CHARSET_ISO_8859_1));

        // Ensure that the set value is the one returned from the getter
        $this->assertSame(Validator::CHARSET_ISO_8859_1, $client->getCharset());
    }

    /**
     * Ensure that the validator sends the correct content type and charset for the parser given
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getCharset
     * @covers \HtmlValidator\Validator::setCharset
     * @covers \HtmlValidator\Validator::getContentTypeString
     * @covers \HtmlValidator\Validator::getMimeTypeForParser
     * @throws ServerException
     */
    public function testValidateDocumentSendsCorrectContentType() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $container = [];
        $httpClientMock = $this->getHttpClientMock(['messages' => []], $container);

        $client->setCharset(Validator::CHARSET_UTF_8);
        $client->setHttpClient($httpClientMock);
        $client->validateDocument($document);

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(['text/html; charset=utf-8'], $request->getHeader('Content-Type'));
        $this->assertSame($document, $request->getBody()->getContents());
        $this->assertSame('out=json&parser=html5', $request->getUri()->getQuery());
    }

    /**
     * Ensure that the validator sends the correct content type and charset for the parser given
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getCharset
     * @covers \HtmlValidator\Validator::setCharset
     * @covers \HtmlValidator\Validator::getContentTypeString
     * @covers \HtmlValidator\Validator::getMimeTypeForParser
     * @throws ServerException
     */
    public function testValidateDocumentSendsCorrectContentTypeWithExplicitCharset() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $container = [];
        $httpClientMock = $this->getHttpClientMock(['messages' => []], $container);
        
        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validateDocument($document);

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(['text/html; charset=iso-8859-1'], $request->getHeader('Content-Type'));
        $this->assertSame($document, $request->getBody()->getContents());
        $this->assertSame('out=json&parser=html5', $request->getUri()->getQuery());
    }

    /**
     * Ensure that the validate() method aliases validateDocument()
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getCharset
     * @covers \HtmlValidator\Validator::setCharset
     * @covers \HtmlValidator\Validator::getContentTypeString
     * @covers \HtmlValidator\Validator::getMimeTypeForParser
     * @covers \HtmlValidator\Validator::validateDocument
     * @covers \HtmlValidator\Validator::validate
     * @throws ServerException
     */
    public function testValidateAliasesValidateDocument() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $container = [];
        $httpClientMock = $this->getHttpClientMock(['messages' => []], $container);

        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validate($document);

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(['text/html; charset=iso-8859-1'], $request->getHeader('Content-Type'));
        $this->assertSame($document, $request->getBody()->getContents());
        $this->assertSame('out=json&parser=html5', $request->getUri()->getQuery());
    }

    /**
     * Ensure that the validator sends the correct content type and charset for the parser given
     *
     * @covers \HtmlValidator\Validator::__construct
     * @covers \HtmlValidator\Validator::getCharset
     * @covers \HtmlValidator\Validator::setCharset
     * @covers \HtmlValidator\Validator::getContentTypeString
     * @covers \HtmlValidator\Validator::getMimeTypeForParser
     * @throws UnknownParserException
     * @throws ServerException
     */
    public function testValidateNodesSendsCorrectRequest() {
        $client = new Validator();

        $nodes = '<item>Those</item><item>Nodes</itme>';

        $container = [];
        $httpClientMock = $this->getHttpClientMock(['messages' => []], $container);

        $client->setParser(Validator::PARSER_XML);
        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validateNodes($nodes);

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(['application/xml; charset=iso-8859-1'], $request->getHeader('Content-Type'));
        $this->assertSame('<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n<root>". $nodes . '</root>', $request->getBody()->getContents());
        $this->assertSame('out=json&parser=xml', $request->getUri()->getQuery());
    }

    /**
     * Get a mocked HTTP client
     *
     * @return Client
     */
    private function getHttpClientMock($body, array &$container = null) {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($body)),
        ]);

        $handler = HandlerStack::create($mock);

        $history = Middleware::history($container);
        $handler->push($history);

        return new Client(['handler' => $handler]);
    }
}