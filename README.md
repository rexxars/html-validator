html-validator
==============

PHP client for the Validator.nu API. Can be configured to use a self-hosted version of the API.

[![Build Status](https://travis-ci.org/rexxars/html-validator.svg?branch=master)](https://travis-ci.org/rexxars/html-validator)

# Usage

``` php
<?php
$document = file_get_contents('my-page.html');

$validator = new HtmlValidator\Validator();
$result = $validator->validateDocument($document);

$result->hasErrors();   // true / false
$result->hasWarnings(); // true / false

$result->getErrors();   // array(HtmlValidator\Message)

echo $result;           // Prints all messages in human-readable format
echo $result->toHTML(); // Prints all messages HTML-formatted
```

# Installing

To include `html-validator` in your project, add it to your `composer.json` file:

```json
{
    "require": {
        "rexxars/html-validator": "~1.0"
    }
}
```

# Example

Document to be validated (`validate-me.html`):
``` html
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Invalid HTML4!</title>
</head>
<body>
    <p>This document is not a proper, well-formed HTML4 document!</p>
    <p>It contains fatal flaws, like:</p>
    <ul>
        <li><div> tags which are not closed</li>
        <li>span-tags which are never opened are attempted closed </span></li>
    </ul>
</body>
</html>
```

Using the validator:
``` php
<?php
$document = file_get_contents('validate-me.html');

$validator = new HtmlValidator\Validator();
$validator->setParser(HtmlValidator\Validator::PARSER_HTML4);
$result = $validator->validateDocument($document);

echo $result;
```

Output:
```
info: HTML4-specific tokenization errors are enabled.


error: End tag “li” seen, but there were open elements.
From line 10, column 44; to line 10, column 48
not closed</li>


error: Unclosed element “div”.
From line 10, column 13; to line 10, column 17
      <li><div> tags

error: Stray end tag “span”.
From line 11, column 67; to line 11, column 73
ed closed </span></li>

```

# Validating a URL

Since 1.1 you can validate URLs as well:

``` php
<?php
$validator = new HtmlValidator\Validator();
$validator->setParser(HtmlValidator\Validator::PARSER_HTML5);
$result = $validator->validateUrl($url);

echo $result;
```

# Using a self-hosted version of the API

Check out [validator.nu](http://about.validator.nu/#src) for instructions on setting up the service.
Once set up, you can configure the validator to use a different host:

``` php
<?php
$validator = new HtmlValidator\Validator('http://self-hosted-validator.domain.com');

```

# License

MIT licensed. See LICENSE for full terms.
