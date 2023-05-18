<?php
/**
 * This file is part of the html-validator package.
 *
 * (c) Espen Hovlandsdal <espen@hovlandsdal.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('FIXTURES_DIR', __DIR__ . '/fixtures');

require dirname(__DIR__).'/vendor/autoload.php';

defined('HTML_VALIDATOR_ENABLE_INTEGRATION_TESTS') || define('HTML_VALIDATOR_ENABLE_INTEGRATION_TESTS', false);
defined('HTML_VALIDATOR_URL') || define('HTML_VALIDATOR_URL', '');
