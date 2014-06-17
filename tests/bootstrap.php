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

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */

defined('HTTPD_SERVER_PATH') || define('HTTPD_SERVER_PATH', __DIR__ . '/validator-server-mock.php');

$autoloader = require __DIR__ . '/../vendor/autoload.php';
$autoloader->add(__NAMESPACE__, __DIR__);
