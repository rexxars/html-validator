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

use Guzzle\Http\Message\Response as HttpResponse;
use HtmlValidator\Exception\ServerException;
use RuntimeException;

/**
 * HTML Validator response
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) Espen Hovlandsdal
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/rexxars/html-validator
 */
class Response {

    /**
     * HTTP response
     *
     * @var Guzzle\Http\Message\Response
     */
    private $httpResponse;

    /**
     * List of errors encountered
     *
     * @var array
     */
    private $errors = array();

    /**
     * List of warnings encountered
     *
     * @var array
     */
    private $warnings = array();

    /**
     * List of all messages encountered
     *
     * @var array
     */
    private $messages = array();

    /**
     * Constructs the response and parses it into usable data
     *
     * @param HttpResponse $response
     */
    public function __construct(HttpResponse $response) {
        $this->httpResponse = $response;

        $this->validateResponse();
        $this->parse();
    }

    /**
     * Validate the HTTP response and throw exceptions on errors
     *
     * @throws ServerException
     */
    private function validateResponse() {
        if ($this->httpResponse->getStatusCode() !== 200) {
            $statusCode = $this->httpResponse->getStatusCode();
            throw new ServerException('Server responded with HTTP status ' . $statusCode, $statusCode);
        } else if (strpos($this->httpResponse->getHeader('Content-Type'), 'application/json') === false) {
            throw new ServerException('Server did not respond with the expected content-type (application/json)');
        }

        try {
            $this->httpResponse->json();
        } catch (RuntimeException $e) {
            throw new ServerException($e->getMessage());
        }
    }

    /**
     * Parse the received response into a usable format
     *
     * @return [type] [description]
     */
    private function parse() {
        $data = $this->httpResponse->json();

        foreach ($data['messages'] as $message) {
            $msg = new Message($message);
            $this->messages[] = $msg;

            if ($message['type'] === 'error' || $message['type'] === 'non-document-error') {
                $this->errors[] = $msg;
            } else if ($message['type'] === 'warning') {
                $this->warnings[] = $msg;
            }
        }
    }

    /**
     * Returns whether the markup the user tried to validate had any errors
     *
     * @return boolean
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Returns whether the markup the user tried to validate had any warnings
     *
     * @return boolean
     */
    public function hasWarnings() {
        return !empty($this->warnings);
    }

    /**
     * Returns whether the markup the user tried to validate resulted in any messages
     *
     * @return boolean
     */
    public function hasMessages() {
        return !empty($this->messages);
    }

    /**
     * Returns all encountered errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Returns all encountered warnings
     *
     * @return array
     */
    public function getWarnings() {
        return $this->warnings;
    }

    /**
     * Returns all encountered messages
     *
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Returns a string-representation of all messages encountered
     *
     * @param  boolean $useHTML Whether to use HTML for formatting
     * @return string
     */
    public function format($useHTML = false) {
        $msgs = array();

        foreach ($this->messages as $msg) {
            $msgs[] = $msg->format($useHTML);
        }

        return implode(PHP_EOL . PHP_EOL, $msgs);
    }

    /**
     * Returns an HTML-representation of all messages encountered
     *
     * @return string
     */
    public function toHTML() {
        return $this->format(true);
    }

    /**
     * Returns a string containing all the messages encountered
     *
     * @return string
     */
    public function __toString() {
        return $this->format();
    }

}