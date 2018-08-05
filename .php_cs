<?php
 $finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'tests');
 return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        'method_argument_space' => ['ensure_fully_multiline' => true],
        'trailing_comma_in_multiline_array' => true,
        'strict_param' => true,
        'phpdoc_order' => true,
        'phpdoc_annotation_without_dot' => true,
        'ordered_imports' => ['sortAlgorithm' => 'length'],
        'concat_space' => ['spacing' => 'one']
    ])
    ->setFinder($finder);
