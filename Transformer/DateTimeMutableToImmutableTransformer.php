<?php

namespace Jane\AutoMapper\Transformer;

use Jane\AutoMapper\Extractor\PropertyMapping;
use Jane\AutoMapper\Generator\UniqueVariableScope;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * Transform DateTime to DateTimeImmutable.
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
final class DateTimeMutableToImmutableTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform(Expr $input, Expr $target, PropertyMapping $propertyMapping, UniqueVariableScope $uniqueVariableScope): array
    {
        return [
            new Expr\StaticCall(new Name\FullyQualified(\DateTimeImmutable::class), 'createFromMutable', [
                new Arg($input),
            ]),
            [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function assignByRef(): bool
    {
        return false;
    }
}
