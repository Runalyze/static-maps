<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR1' => true,
        '@PSR2' => true,
        'header_comment' => [
            'header' => <<<EOF
This file is part of the StaticMaps.

(c) RUNALYZE <mail@runalyze.com>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF
        ],
        'ordered_imports' => true,
        'phpdoc_separation' => false,
    ))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    )
;
