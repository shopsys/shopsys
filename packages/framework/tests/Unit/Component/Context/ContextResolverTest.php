<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Context;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolver;

class ContextResolverTest extends TestCase
{
    public function testConstructorAddsContexts(): void
    {
        $context = new TestContextA(shouldMatch: true);
        $resolver = new ContextResolver([$context]);

        // Test that context was registered correctly by checking if it matches
        $this->assertTrue($resolver->isCurrentContext(TestContextA::class));
    }

    public function testConstructorThrowsExceptionForDuplicateContexts(): void
    {
        $context1 = new TestContextA();
        $context2 = new TestContextA();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context with identifier "' . TestContextA::class . '" is already registered');

        new ContextResolver([$context1, $context2]);
    }

    public function testContextMatching(): void
    {
        $context1 = new TestContextA(shouldMatch: false);
        $context2 = new TestContextB(shouldMatch: true);
        $context3 = new TestContextC(shouldMatch: true);

        $resolver = new ContextResolver([$context1, $context2, $context3]);

        $this->assertFalse($resolver->isCurrentContext(TestContextA::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextB::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextC::class));
    }

    public function testContextWithRequiredContexts(): void
    {
        $contextWithDependency = new TestContextA(shouldMatch: true, requiredContexts: [TestContextB::class]);
        $requiredContext = new TestContextB(shouldMatch: true);
        $independentContext = new TestContextC(shouldMatch: true);

        $resolver = new ContextResolver([$contextWithDependency, $requiredContext, $independentContext]);

        $this->assertTrue($resolver->isCurrentContext(TestContextA::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextB::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextC::class));
    }

    public function testContextWithNonMatchingRequiredContext(): void
    {
        $nonMatchingRequiredContext = new TestContextB(shouldMatch: false);
        $contextWithDependency = new TestContextA(shouldMatch: true, requiredContexts: [TestContextB::class]);

        $resolver = new ContextResolver([$nonMatchingRequiredContext, $contextWithDependency]);

        $this->assertFalse($resolver->isCurrentContext(TestContextA::class));
        $this->assertFalse($resolver->isCurrentContext(TestContextB::class));
    }

    public function testContextMatchingResultsAreCached(): void
    {
        $matchesCallCount = 0;

        $context = new class($matchesCallCount) extends AbstractContext {
            public function __construct(private int &$matchesCallCount)
            {
            }

            #[Override]
            public function matches(): bool
            {
                $this->matchesCallCount++;

                return true;
            }

            public function getDescription(): string
            {
                return 'Test context for caching';
            }
        };

        $resolver = new ContextResolver([$context]);

        $contextClass = $context::class;
        $resolver->isCurrentContext($contextClass);
        $resolver->isCurrentContext($contextClass);

        $this->assertSame(1, $matchesCallCount, 'matches() should only be called once due to caching');
    }

    public function testComplexDependencyChain(): void
    {
        $rootContext = new TestContextC(shouldMatch: true);
        $middleContext = new TestContextB(shouldMatch: true, requiredContexts: [TestContextC::class]);
        $leafContext = new TestContextA(shouldMatch: true, requiredContexts: [TestContextB::class]);

        $resolver = new ContextResolver([$rootContext, $middleContext, $leafContext]);

        $this->assertTrue($resolver->isCurrentContext(TestContextC::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextB::class));
        $this->assertTrue($resolver->isCurrentContext(TestContextA::class));
    }

    public function testCircularDependencyDetection(): void
    {
        $contextA = new TestContextA(shouldMatch: true, requiredContexts: [TestContextB::class]);
        $contextB = new TestContextB(shouldMatch: true, requiredContexts: [TestContextA::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Circular dependency detected: ' . TestContextA::class . ' → ' . TestContextB::class . ' → ' . TestContextA::class);

        new ContextResolver([$contextA, $contextB]);
    }

    public function testComplexCircularDependencyDetection(): void
    {
        $contextA = new TestContextA(shouldMatch: false, requiredContexts: [TestContextB::class]);
        $contextB = new TestContextB(shouldMatch: false, requiredContexts: [TestContextC::class]);
        $contextC = new TestContextC(shouldMatch: false, requiredContexts: [TestContextA::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Circular dependency detected: ' . TestContextA::class . ' → ' . TestContextB::class . ' → ' . TestContextC::class . ' → ' . TestContextA::class);

        new ContextResolver([$contextA, $contextB, $contextC]);
    }

    public function testCircularDependencyInMiddleOfChain(): void
    {
        $contextA = new TestContextA(shouldMatch: true, requiredContexts: [TestContextB::class]);
        $contextB = new TestContextB(shouldMatch: false, requiredContexts: [TestContextC::class]);
        $contextC = new TestContextC(shouldMatch: true, requiredContexts: [TestContextB::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Circular dependency detected: ' . TestContextB::class . ' → ' . TestContextC::class . ' → ' . TestContextB::class);

        new ContextResolver([$contextA, $contextB, $contextC]);
    }
}
