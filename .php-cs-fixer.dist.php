<?php

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->append([__DIR__.'.php-cs-fixer.dist.php'])
    )
;
