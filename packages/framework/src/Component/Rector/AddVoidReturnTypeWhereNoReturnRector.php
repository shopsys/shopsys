<?php

declare (strict_types=1);
namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver, ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver, ReflectionResolver $reflectionResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type void to function like without any return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues()
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues(): void
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        if (!$this->silentVoidResolver->hasExclusiveVoid($node)) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnVendorLockResolver->isVendorLocked($node)) {
            return null;
        }
        $node->returnType = new Identifier('void');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VOID_TYPE;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function shouldSkipClassMethod($functionLike) : bool
    {
        if (!$functionLike instanceof ClassMethod) {
            return \false;
        }
        if ($functionLike->isMagic()) {
            return \true;
        }
        if ($functionLike->isAbstract()) {
            return \true;
        }
        return \false;
    }
}
