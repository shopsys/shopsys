<?php

namespace Tests\FrameworkBundle\Unit\Component\Filesystem;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;

class FilepathComparatorTest extends TestCase
{
    public function testIsPathWithinDirectoryThrowsExceptionWithNonExistentDirectoryPath(): void
    {
        $filepathComparator = new FilepathComparator();

        $path = 'anyPath';
        $nonExistentPath = 'nonExistentPath';

        $this->expectException(DirectoryDoesNotExistException::class);
        $filepathComparator->isPathWithinDirectory($path, $nonExistentPath);
    }

    public function testIsPathWithinAnotherExistingPathReturnsTrueForFileInsideDirectory(): void
    {
        $filepathComparator = new FilepathComparator();

        $path = $this->getResourcePath('dir/fileInside');
        $directoryPath = $this->getResourcePath('dir');

        $this->assertTrue($filepathComparator->isPathWithinDirectory($path, $directoryPath));
    }

    public function testIsPathWithinAnotherExistingPathReturnsFalseForFileOutsideDirectory(): void
    {
        $filepathComparator = new FilepathComparator();

        $path = $this->getResourcePath('fileOutside');
        $directoryPath = $this->getResourcePath('dir');

        $this->assertFalse($filepathComparator->isPathWithinDirectory($path, $directoryPath));
    }

    public function testIsPathWithinAnotherExistingPathReturnsTrueForDirectorySelf(): void
    {
        $filepathComparator = new FilepathComparator();

        $path = $this->getResourcePath('dir');
        $directoryPath = $this->getResourcePath('dir');

        $this->assertTrue($filepathComparator->isPathWithinDirectory($path, $directoryPath));
    }

    public function testIsPathWithinAnotherExistingPathReturnsTrueForNonExistentFileInsideDirectory(): void
    {
        $filepathComparator = new FilepathComparator();

        $path = $this->getResourcePath('dir/nonexistentFileInside');
        $directoryPath = $this->getResourcePath('dir');

        $this->assertTrue($filepathComparator->isPathWithinDirectory($path, $directoryPath));
    }
    
    private function getResourcePath(string $relativePath): string
    {
        return __DIR__ . '/Resources/' . $relativePath;
    }
}
