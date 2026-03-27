<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder;
use Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\StaleAnnotationsRemover;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\FrameworkParentClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\FrameworkParentWithMagicMethodInDocBlock;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildAllStale;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildAllValid;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildWithMagicMethodInDocBlock;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildWithNoDocBlock;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildWithNonAnnotationComment;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest\ProjectChildWithOwnMethod;

class StaleAnnotationsRemoverTest extends TestCase
{
    protected function createStaleAnnotationsRemover(): StaleAnnotationsRemover
    {
        $fileContentsReplacer = $this->createStub(FileContentsReplacer::class);
        $annotationsAdder = new AnnotationsAdder($fileContentsReplacer);

        return new StaleAnnotationsRemover($fileContentsReplacer, $annotationsAdder);
    }

    public function testNoDocBlockReturnsNoStaleAnnotations(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildWithNoDocBlock::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertSame([], $staleLines);
    }

    public function testAllValidAnnotationsReturnsNoStaleAnnotations(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildAllValid::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertSame([], $staleLines);
    }

    public function testDetectsStaleMethodAnnotation(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildClass::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertCount(2, $staleLines);
        $this->assertContains(' * @property string $staleProperty', $staleLines);
        $this->assertContains(' * @method void staleMethod()', $staleLines);
    }

    public function testMethodDeclaredOnProjectClassIsNotStale(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildWithOwnMethod::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertCount(1, $staleLines);
        $this->assertContains(' * @method void staleMethod()', $staleLines);
    }

    public function testMethodDocumentedOnlyOnFrameworkClassDocBlockIsNotStale(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentWithMagicMethodInDocBlock::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildWithMagicMethodInDocBlock::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertSame([], $staleLines);
    }

    public function testNonMethodPropertyAnnotationsAreNeverStale(): void
    {
        $remover = $this->createStaleAnnotationsRemover();
        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildWithNonAnnotationComment::class);

        $staleLines = $remover->getStaleAnnotationLines($frameworkClass, $projectClass);

        $this->assertCount(1, $staleLines);
        $this->assertContains(' * @method void staleMethod()', $staleLines);
    }

    public function testRemoveStaleAnnotationsCallsFileReplacer(): void
    {
        $fileContentsReplacerMock = $this->createMock(FileContentsReplacer::class);
        $annotationsAdder = new AnnotationsAdder($this->createStub(FileContentsReplacer::class));
        $remover = new StaleAnnotationsRemover($fileContentsReplacerMock, $annotationsAdder);

        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildClass::class);

        $originalDocBlock = $projectClass->getDocComment();
        $expectedDocBlock = "/**\n * @property string \$validProperty\n * @method void validMethod()\n * @method \\App\\Model\\Category\\CategoryFacade getCategory()\n */";

        $fileContentsReplacerMock->expects($this->once())->method('replaceInFile')->with(
            $projectClass->getFileName(),
            $originalDocBlock,
            $expectedDocBlock,
        );

        $remover->removeStaleAnnotationsFromClass($frameworkClass, $projectClass);
    }

    public function testRemoveAllStaleAnnotationsRemovesEntireDocBlock(): void
    {
        $fileContentsReplacerMock = $this->createMock(FileContentsReplacer::class);
        $annotationsAdder = new AnnotationsAdder($this->createStub(FileContentsReplacer::class));
        $remover = new StaleAnnotationsRemover($fileContentsReplacerMock, $annotationsAdder);

        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildAllStale::class);

        $originalDocBlock = $projectClass->getDocComment();

        $fileContentsReplacerMock->expects($this->once())->method('replaceInFile')->with(
            $projectClass->getFileName(),
            $originalDocBlock . "\n",
            '',
        );

        $remover->removeStaleAnnotationsFromClass($frameworkClass, $projectClass);
    }

    public function testRemoveStaleAnnotationsNoOpWhenNothingStale(): void
    {
        $fileContentsReplacerMock = $this->createMock(FileContentsReplacer::class);
        $annotationsAdder = new AnnotationsAdder($this->createStub(FileContentsReplacer::class));
        $remover = new StaleAnnotationsRemover($fileContentsReplacerMock, $annotationsAdder);

        $frameworkClass = ReflectionObject::createFromName(FrameworkParentClass::class);
        $projectClass = ReflectionObject::createFromName(ProjectChildAllValid::class);

        $fileContentsReplacerMock->expects($this->never())->method('replaceInFile');

        $remover->removeStaleAnnotationsFromClass($frameworkClass, $projectClass);
    }
}
