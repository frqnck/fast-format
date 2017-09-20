FastFormat, lean and fast string formatters!
=

[![Latest Stable Version](https://poser.pugx.org/frqnck/string-template/v/stable.svg)](https://packagist.org/packages/frqnck/string-template)  [![Build Status](https://travis-ci.org/frqnck/string-template.png?branch=master)](https://travis-ci.org/frqnck/string-template)  [![Code Quality](https://scrutinizer-ci.com/g/frqnck/string-template/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/frqnck/string-template/?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/frqnck/apix-log/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/frqnck/string-template/?branch=master)  [![License](https://poser.pugx.org/frqnck/string-template/license.svg)](https://packagist.org/packages/frqnck/string-template)

An expressive and **fast** string templating engine.

* 100% Unit **tested** and compliant with PSR0, PSR1 and PSR2.
* Continuously integrated against **PHP 5.3**, **5.4**, **5.5**, **5.6**, **7.0** and **HHVM**.
* Available as a [Composer](https://packagist.org/packages/frqnck/string-template) package.

Feel free to comment, send pull requests and patches...

Usage
-----------
```php
use Apix\String;

$template = new Template;
$template->render("My name is {name} {surname}", ['name' => 'franck', 'surname' => 'Cassedanne']);

```

Installation
------------------------

Install the current major version using Composer with (recommended)
```
$ composer require frqnck/string-template:1.0.*
```
Or install the latest stable version with
```
$ composer require frqnck/string-template
```

License
-------
Pastis is licensed under the New BSD license -- see the `LICENSE.txt` for the full license details.
