<?php

return (new PhpCsFixer\Config)
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__)->exclude('tests/snapshots'))
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'align_multiline_comment' => [
            'comment_type' => 'all_multiline',
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '|' => 'single_space', // Doesn't apply to union types
            ],
        ],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw'],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'get_class_to_class_keyword' => true,
        'global_namespace_import' => [
            'import_classes' => true,
        ],
        'new_with_braces' => false,
        'no_empty_comment' => false,
        'no_useless_else' => true,
        'not_operator_with_successor_space' => true,
        'php_unit_method_casing' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_to_comment' => [
            'ignored_tags' => ['var'],
        ],
        'phpdoc_separation' => [
            'groups' => [
                ['test', 'group', 'dataProvider', 'doesNotPerformAssertions'],
            ],
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'yoda_style' => false,
    ]);
