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

}