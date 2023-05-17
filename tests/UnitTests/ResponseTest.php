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

use HtmlValidator\Exception\ServerException;
use HtmlValidator\Response;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class ResponseTest extends TestCase {

    /**
     * Ensure construction of non-200 response throws ServerException
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     *
     * @throws ServerException
     */
    public function testWillThrowOnNon200Reponse() {
        $this->expectException(ServerException::class);
        new Response(new Psr7Response(500));
    }

    /**
     * Ensure construction of response with a non-JSON response throws ServerException
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     *
     * @throws ServerException
     */
    public function testWillThrowOnNonJsonResponse() {
        $this->expectException(ServerException::class);
        new Response(new Psr7Response(200, ['Content-Type' => 'text/html']));
    }

    /**
     * Ensure construction of response with an invalid JSON body throws ServerException
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     *
     * @throws ServerException
     */
    public function testWillThrowOnInvalidJsonResponse() {
        $this->expectException(ServerException::class);
        new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], '{"incompl'));
    }

    /**
     * Ensure population of errors
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     * @covers \HtmlValidator\Response::parse
     * @covers \HtmlValidator\Response::getErrors
     * @covers \HtmlValidator\Response::hasErrors
     * @throws ServerException
     */
    public function testWillPopulateErrors() {
        $data = [
            'messages' => [
                [
                    'type' => 'error',
                    'firstLine' => 1,
                    'lastLine' => 2,
                    'firstColumn' => 3,
                    'lastColumn' => 4,
                    'hiliteStart' => 5,
                    'hiliteLength' => 6,
                    'message' => 'Foobar',
                    'extract' => '<strong>Foo</strong>',
                ],
                [
                    'type' => 'error',
                    'firstLine' => 9,
                    'lastLine' => 8,
                    'firstColumn' => 7,
                    'lastColumn' => 6,
                    'hiliteStart' => 5,
                    'hiliteLength' => 4,
                    'message' => 'Pimp Pelican',
                    'extract' => '<em>Pelican</em>',
                ],
            ],
        ];

        $response = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], json_encode($data)));

        $errors = $response->getErrors();
        $this->assertTrue($response->hasErrors());
        $this->assertCount(2, $errors);
        $this->assertSame($data['messages'][0]['message'], $errors[0]->getText());
        $this->assertSame($data['messages'][1]['message'], $errors[1]->getText());
    }

    /**
     * Ensure population of warnings
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     * @covers \HtmlValidator\Response::parse
     * @covers \HtmlValidator\Response::getWarnings
     * @covers \HtmlValidator\Response::hasWarnings
     * @throws ServerException
     */
    public function testWillPopulateWarnings() {
        $data = array(
            'messages' => array(
                array(
                    'type' => 'warning',
                    'firstLine' => 1,
                    'lastLine' => 2,
                    'firstColumn' => 3,
                    'lastColumn' => 4,
                    'hiliteStart' => 5,
                    'hiliteLength' => 6,
                    'message' => 'Foobar',
                    'extract' => '<strong>Foo</strong>',
                ),
                array(
                    'type' => 'warning',
                    'firstLine' => 9,
                    'lastLine' => 8,
                    'firstColumn' => 7,
                    'lastColumn' => 6,
                    'hiliteStart' => 5,
                    'hiliteLength' => 4,
                    'message' => 'Pimp Pelican',
                    'extract' => '<em>Pelican</em>',
                ),
            ),
        );

        $response = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], json_encode($data)));

        $warnings = $response->getWarnings();
        $this->assertTrue($response->hasWarnings());
        $this->assertCount(2, $warnings);
        $this->assertSame($data['messages'][0]['message'], $warnings[0]->getText());
        $this->assertSame($data['messages'][1]['message'], $warnings[1]->getText());
    }

    /**
     * Ensure population of messages
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     * @covers \HtmlValidator\Response::parse
     * @covers \HtmlValidator\Response::getMessages
     * @covers \HtmlValidator\Response::hasMessages
     *
     * @throws ServerException
     */
    public function testWillPopulateMessages() {
        $data = array(
            'messages' => array(
                array(
                    'type' => 'non-document-error',
                    'firstLine' => 1,
                    'lastLine' => 2,
                    'firstColumn' => 3,
                    'lastColumn' => 4,
                    'hiliteStart' => 5,
                    'hiliteLength' => 6,
                    'message' => 'Foobar message',
                    'extract' => '<strong>Foo</strong>',
                ),
                array(
                    'type' => 'warning',
                    'firstLine' => 9,
                    'lastLine' => 8,
                    'firstColumn' => 7,
                    'lastColumn' => 6,
                    'hiliteStart' => 5,
                    'hiliteLength' => 4,
                    'message' => 'Pimp Pelican warning',
                    'extract' => '<em>Pelican</em>',
                ),
                array(
                    'type' => 'error',
                    'firstLine' => 9,
                    'lastLine' => 8,
                    'firstColumn' => 7,
                    'lastColumn' => 6,
                    'hiliteStart' => 5,
                    'hiliteLength' => 4,
                    'message' => 'Pimp Pelican error',
                    'extract' => '<em>Pelican</em>',
                ),
            ),
        );

        $response = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], json_encode($data)));

        $messages = $response->getMessages();
        $this->assertTrue($response->hasMessages());
        $this->assertCount(3, $messages);
        $this->assertSame($data['messages'][0]['message'], $messages[0]->getText());
        $this->assertSame($data['messages'][1]['message'], $messages[1]->getText());
        $this->assertSame($data['messages'][2]['message'], $messages[2]->getText());
    }

    /**
     * Test proper formatting of errors/warnings
     *
     * @covers \HtmlValidator\Response::__construct
     * @covers \HtmlValidator\Response::validateResponse
     * @covers \HtmlValidator\Response::parse
     * @covers \HtmlValidator\Response::format
     * @covers \HtmlValidator\Response::__toString
     * @covers \HtmlValidator\Response::toHTML
     *
     * @throws ServerException
     */
    public function testWillFormat() {
        $data = array(
            'messages' => array(
                array(
                    'type' => 'warning',
                    'firstLine' => 1,
                    'lastLine' => 2,
                    'firstColumn' => 3,
                    'lastColumn' => 4,
                    'hiliteStart' => 5,
                    'hiliteLength' => 6,
                    'message' => 'Foobar',
                    'extract' => '<strong>Foo</strong>',
                ),
                array(
                    'type' => 'error',
                    'firstLine' => 9,
                    'lastLine' => 8,
                    'firstColumn' => 7,
                    'lastColumn' => 6,
                    'hiliteStart' => 9,
                    'hiliteLength' => 7,
                    'message' => 'Pimp Pelican',
                    'extract' => '<em>Pimp Pelican</em>',
                ),
            ),
        );

        $response = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], json_encode($data)));

        // Plain text
        $expected  = 'warning: Foobar' . PHP_EOL;
        $expected .= 'From line 1, column 3; to line 2, column 4' . PHP_EOL;
        $expected .= '<strong>Foo</strong>' . PHP_EOL . PHP_EOL;

        $expected .= 'error: Pimp Pelican' . PHP_EOL;
        $expected .= 'From line 9, column 7; to line 8, column 6' . PHP_EOL;
        $expected .= '<em>Pimp Pelican</em>';

        $this->assertSame($expected, (string) $response);

        // HTML
        $expected  = '<strong>warning</strong>: Foobar<br>' . PHP_EOL;
        $expected .= 'From line 1, column 3; to line 2, column 4<br>' . PHP_EOL;
        $expected .= '&lt;stro<span class="highlight">ng&gt;Foo</span>&lt;/strong&gt;' . PHP_EOL . PHP_EOL;

        $expected .= '<strong>error</strong>: Pimp Pelican<br>' . PHP_EOL;
        $expected .= 'From line 9, column 7; to line 8, column 6<br>' . PHP_EOL;
        $expected .= '&lt;em&gt;Pimp <span class="highlight">Pelican</span>&lt;/em&gt;';

        $this->assertSame($expected, $response->toHTML());
    }
}