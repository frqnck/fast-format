<?php
namespace Apix\String;

use Apix\Benchmark;
use StringTemplate;

require_once '../vendor/autoload.php';
require 'Benchmark.php';
require 'StringTemplate/vendor/autoload.php';

echo "--";

$tokens = [
    'first' => 'loulou',
    'last' => 'cassedanne',
    'age' => '10',
    'items' => [
        'qqq', 'zzz'
    ],
    'siblings' => [
        [ 
            'first' => 'm0lly',
            'last' => 'cassedanne',
            'age' => 10
        ],
        [ 
            'first' => 'juju',
            'last' => 'cassedanne',
            'age' => 7
        ]
    ]
];
$format = 'Hi my name is {first|ucfirst|strrev} {last|ucfirst}. ';
$format .= 'I have {siblings|count} siblings. ';
$format .= 'And they are: {siblings.0.first} ({siblings.0.age}), {siblings.1.first} ({siblings.0.age})';
$format .= 'I can speak french: "Bonjours mon nom est {first|ucfirst} {last|ucfirst}...". ';

$format = 'Siblings: {items|implode}';

$template = new Template(new Filter\PlainPhp);
echo $template->parse($format)->render($tokens);
echo "--";
exit;