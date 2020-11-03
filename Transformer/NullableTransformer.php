<?php

namespace Jane\AutoMapper\Transformer;

use Jane\AutoMapper\Extractor\PropertyMapping;
use Jane\AutoMapper\Generator\UniqueVariableScope;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

/**
 * Tansformer decorator to handle null values.
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
final class NullableTransformer implements TransformerInterface
{
    private $itemTransformer;
    private $isTargetNullable;

    public function __construct(TransformerInterface $itemTransformer, bool $isTargetNullable)
    {
        $this->itemTransformer = $itemTransformer;
        $this->isTargetNullable = $isTargetNullable;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Expr $input, Expr $target, PropertyMapping $propertyMapping, UniqueVariableScope $uniqueVariableScope): array
    {
        [$output, $itemStatements] = $this->itemTransformer->transform($input, $target, $propertyMapping, $uniqueVariableScope);

        $newOutput = null;
        $statements = [];
        $assignClass = $this->itemTransformer->assignByRef() ? Expr\AssignRef::class : Expr\Assign::class;

        if ($this->isTargetNullable) {
            $newOutput = new Expr\Variable($uniqueVariableScope->getUniqueName('value'));
            $statements[] = new Stmt\Expression(new Expr\Assign($newOutput, new Expr\ConstFetch(new Name('null'))));
            $itemStatements[] = new Stmt\Expression(new $assignClass($newOutput, $output));
        }

        $statements[] = new Stmt\If_(new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $input), [
            'stmts' => $itemStatements,
        ]);

        return [$newOutput ?? $output, $statements];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->itemTransformer->getDependencies();
    }

    /**
     * {@inheritdoc}
     */
    public function assignByRef(): bool
    {
        return false;
    }
}
