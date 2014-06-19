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

use Guzzle\Common\Exception\RuntimeException;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * Ensure the client can be instantiated without errors with no arguments passed
     * 
     * @covers HtmlValidator\Validator::__construct
     */
    public function testCanConstructClientWithDefaultArguments() {
        $client = new Validator();
        $this->assertInstanceOf('HtmlValidator\Validator', $client);
    }

    /**
     * Test that we can both set and get the parsers to be used when validating
     * 
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getParser
     * @covers HtmlValidator\Validator::setParser
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
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getCharset
     * @covers HtmlValidator\Validator::setCharset
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
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getCharset
     * @covers HtmlValidator\Validator::setCharset
     * @covers HtmlValidator\Validator::getContentTypeString
     * @covers HtmlValidator\Validator::getMimeTypeForParser
     */
    public function testValidateDocumentSendsCorrectContentType() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $responseMock = $this->getGuzzleResponseMock(array('messages' => array()));
        $requestMock = $this->getGuzzleRequestMock($responseMock);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(''),
                $this->equalTo(array(
                    'Content-Type' => 'text/html; charset=utf-8'
                )),
                $this->equalTo($document),
                $this->equalTo(array(
                    'query' => array(
                        'out'    => 'json',
                        'parser' => 'html5'
                    )
                ))
            )
            ->will($this->returnValue($requestMock));
        
        $client->setCharset(Validator::CHARSET_UTF_8);
        $client->setHttpClient($httpClientMock);
        $client->validateDocument($document);
    }

    /**
     * Ensure that the validator sends the correct content type and charset for the parser given
     * 
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getCharset
     * @covers HtmlValidator\Validator::setCharset
     * @covers HtmlValidator\Validator::getContentTypeString
     * @covers HtmlValidator\Validator::getMimeTypeForParser
     */
    public function testValidateDocumentSendsCorrectContentTypeWithExplicitCharset() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $responseMock = $this->getGuzzleResponseMock(array('messages' => array()));
        $requestMock = $this->getGuzzleRequestMock($responseMock);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(''),
                $this->equalTo(array(
                    'Content-Type' => 'text/html; charset=iso-8859-1'
                )),
                $this->equalTo($document),
                $this->equalTo(array(
                    'query' => array(
                        'out'    => 'json',
                        'parser' => 'html5'
                    )
                ))
            )
            ->will($this->returnValue($requestMock));
        
        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validateDocument($document);
    }

    /**
     * Ensure that the validate() method aliases validateDocument()
     * 
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getCharset
     * @covers HtmlValidator\Validator::setCharset
     * @covers HtmlValidator\Validator::getContentTypeString
     * @covers HtmlValidator\Validator::getMimeTypeForParser
     * @covers HtmlValidator\Validator::validateDocument
     * @covers HtmlValidator\Validator::validate
     */
    public function testValidateAliasesValidateDocument() {
        $client = new Validator();

        $document = '<p>Dat document</p>';

        $responseMock = $this->getGuzzleResponseMock(array('messages' => array()));
        $requestMock = $this->getGuzzleRequestMock($responseMock);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(''),
                $this->equalTo(array(
                    'Content-Type' => 'text/html; charset=iso-8859-1'
                )),
                $this->equalTo($document),
                $this->equalTo(array(
                    'query' => array(
                        'out'    => 'json',
                        'parser' => 'html5'
                    )
                ))
            )
            ->will($this->returnValue($requestMock));
        
        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validate($document);
    }

    /**
     * Ensure that the validator sends the correct content type and charset for the parser given
     * 
     * @covers HtmlValidator\Validator::__construct
     * @covers HtmlValidator\Validator::getCharset
     * @covers HtmlValidator\Validator::setCharset
     * @covers HtmlValidator\Validator::getContentTypeString
     * @covers HtmlValidator\Validator::getMimeTypeForParser
     */
    public function testValidateNodesSendsCorrectRequest() {
        $client = new Validator();

        $nodes = '<item>Those</item><item>Nodes</itme>';

        $responseMock = $this->getGuzzleResponseMock(array('messages' => array()));
        $requestMock = $this->getGuzzleRequestMock($responseMock);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(''),
                $this->equalTo(array(
                    'Content-Type' => 'application/xml; charset=iso-8859-1'
                )),
                $this->stringContains($nodes),
                $this->equalTo(array(
                    'query' => array(
                        'out'    => 'json',
                        'parser' => 'xml'
                    )
                ))
            )
            ->will($this->returnValue($requestMock));
        
        $client->setParser(Validator::PARSER_XML);
        $client->setCharset(Validator::CHARSET_ISO_8859_1);
        $client->setHttpClient($httpClientMock);
        $client->validateNodes($nodes);
    }

    /**
     * Get a mocked HTTP client
     * 
     * @return Guzzle\Http\Client
     */
    private function getHttpClientMock() {
        $mock = ($this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock());

        return $mock;
    }

    /**
     * Get a guzzle response mock
     *
     * @param  mixed $body Request body
     * @return Guzzle\Http\Message\Response
     */
    private function getGuzzleResponseMock($body) {
        $mock = ($this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock());

        $mock
            ->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $mock
            ->expects($this->any())
            ->method('getHeader')
            ->with($this->equalTo('Content-Type'))
            ->will($this->returnValue('application/json'));

        $mock
            ->expects($this->any())
            ->method('json')
            ->will($this->returnValue($body));

        return $mock;
    }

    /**
     * Get a guzzle request mock
     *
     * @param  Guzzle\Http\Message\Response $response Response to return when calling send()
     * @return Guzzle\Http\Message\Request
     */
    private function getGuzzleRequestMock($response) {
        $mock = ($this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock());

        $mock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));

        return $mock;
    }

}