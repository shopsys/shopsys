<?php

declare(strict_types=1);

namespace Sniffer;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class FrontendApiNamespaceSniffer implements Sniff
{
    private const RESOLVER_NAMESPACE_PART = 'Resolver';
    private const RESOLVER_SERVICE_FILE = 'Resolver.php';

    private const MUTATION_NAMESPACE_PART = 'Mutation';
    private const MUTATION_SERVICE_FILE = 'Mutation.php';

    private const FRONTEND_API_NAMESPACE_PART = 'FrontendApi';

    /**
     * @return array
     */
    public function register(): array
    {
        return [
            T_NAMESPACE,
        ];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     */
    public function process(File $file, $position): void
    {

        if ($this->stringEndsWith($file->getFilename(), self::RESOLVER_SERVICE_FILE) !== false) {
            $this->processResolver($file, $position);
        }

        if ($this->stringEndsWith($file->getFilename(), self::MUTATION_SERVICE_FILE) !== false) {
            $this->processMutation($file, $position);
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     */
    private function processMutation(File $file, int $position): void
    {
        $this->processCurrentNamespacePart($file, $position, self::MUTATION_NAMESPACE_PART);
        $this->processDirectionApiNamespaceParts($file, $position, self::MUTATION_NAMESPACE_PART);
        $this->processParentFrontendApiNamespacePart(
            $file,
            $position,
            $this->getNamespaceOfFile($file, $position),
            self::MUTATION_NAMESPACE_PART
        );
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     */
    private function processResolver(File $file, int $position): void
    {
        $actualNamespace = $this->getNamespaceOfFile($file, $position);

        if (strpos($actualNamespace, self::RESOLVER_NAMESPACE_PART) === false
            &&
            strpos($actualNamespace, self::FRONTEND_API_NAMESPACE_PART) === false
        ) {
            return;
        }

        $this->processCurrentNamespacePart($file, $position, self::RESOLVER_NAMESPACE_PART);
        $this->processDirectionApiNamespaceParts($file, $position, self::RESOLVER_NAMESPACE_PART);
        $this->processParentFrontendApiNamespacePart(
            $file,
            $position,
            $actualNamespace,
            self::RESOLVER_NAMESPACE_PART
        );
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @param string $checkedNamespacePart
     */
    private function processCurrentNamespacePart(File $file, int $position, string $checkedNamespacePart): void
    {
        $namespacePartsIndexedByPosition = $this->getNamespacePartsIndexedByPosition($file, $position);
        if (in_array($checkedNamespacePart, $namespacePartsIndexedByPosition, true) !== false) {
            return;
        }

        $className = $this->getClassnameOfFile($file);

        $error = sprintf(
            'Class %s should be in namespace contains \'%s\' part: App\\...\\%s\\%s\\%s',
            $className,
            $checkedNamespacePart,
            self::FRONTEND_API_NAMESPACE_PART,
            $checkedNamespacePart,
            $className
        );

        $file->addError($error, $position, 'missingNamespace');
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @param string $checkedNamespacePart
     */
    private function processDirectionApiNamespaceParts(File $file, int $position, string $checkedNamespacePart): void
    {
        $namespacePartsIndexedByPosition = $this->getNamespacePartsIndexedByPosition($file, $position);

        $checkApiNamespace = false;
        $checkedNamespacePartKey = null;
        for (
            $namespacePart = reset($namespacePartsIndexedByPosition);
            $namespacePart !== false;
            $namespacePart = next($namespacePartsIndexedByPosition)
        ) {
            if ($namespacePart === $checkedNamespacePart) {
                $checkApiNamespace = true;
                $checkedNamespacePartKey = key($namespacePartsIndexedByPosition);
            }

            if ($checkApiNamespace && $namespacePart === self::FRONTEND_API_NAMESPACE_PART) {
                $error = sprintf(
                    'Class %s has wrong namespace ordering. Namespace part \'%s\' shouldn\'t be after \'%s\': App\\...\\%s\\%s\\%s',
                    $this->getClassnameOfFile($file),
                    self::FRONTEND_API_NAMESPACE_PART,
                    $checkedNamespacePart,
                    self::FRONTEND_API_NAMESPACE_PART,
                    $checkedNamespacePart,
                    $this->getClassnameOfFile($file)
                );
                $file->addError($error, $checkedNamespacePartKey, 'wrongNamespaceOrdering');

                break;
            }
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @param string $actualNamespace
     * @param string $checkedNamespacePart
     */
    private function processParentFrontendApiNamespacePart(File $file, int $position, string $actualNamespace, string $checkedNamespacePart): void
    {
        $namespacePartsIndexedByPosition = $this->getNamespacePartsIndexedByPosition($file, $position);


        $namespacePart = reset($namespacePartsIndexedByPosition);
        while ($namespacePart !== false) {
            if ($namespacePart === $checkedNamespacePart) {
                $previousNamespacePart = prev($namespacePartsIndexedByPosition);
                if ($previousNamespacePart !== self::FRONTEND_API_NAMESPACE_PART) {
                    $previousKey = key($namespacePartsIndexedByPosition);
                    $className = $this->getClassnameOfFile($file);
                    $error = sprintf(
                        'Class %s has probably wrong namespace %s. Parent of \'%s\' namespace part should be \'FrontendApi\': %s\\%s',
                        $className,
                        $actualNamespace,
                        $checkedNamespacePart,
                        $this->getCorrectNamespace($namespacePartsIndexedByPosition),
                        $className
                    );
                    $file->addError($error, $previousKey, 'missingApiNamespace');
                }
                break;
            }
            $namespacePart = next($namespacePartsIndexedByPosition);
        }
    }

    /**
     * @param string[] $namespacePartsIndexedByPosition
     * @return string
     */
    private function getCorrectNamespace(array $namespacePartsIndexedByPosition): string
    {
        $correctNamespaceParts = [];
        foreach ($namespacePartsIndexedByPosition as $part) {
            if ($part === self::RESOLVER_NAMESPACE_PART) {
                $correctNamespaceParts = ['...',self::FRONTEND_API_NAMESPACE_PART];
            }
            $correctNamespaceParts[] = $part;
        }

        return implode('\\', $correctNamespaceParts);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @return string
     */
    private function getClassnameOfFile(File $file): string
    {
        $position = $file->findNext([T_CLASS], 0);
        $position = $file->findNext([T_STRING], $position);
        $token = $file->getTokens()[$position];

        return $token['content'];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @return string[]
     */
    private function getNamespacePartsIndexedByPosition(File $file, int $position): array
    {
        $position = $file->findNext([T_STRING], $position);
        $token = $file->getTokens()[$position];
        $namespacePartsIndexedByPosition = [];
        do {
            if ($token['code'] === T_STRING) {
                $namespacePartsIndexedByPosition[$position] = $token['content'];
            }
            $position++;
            $token = $file->getTokens()[$position];
        } while ($token['code'] !== T_SEMICOLON);

        return $namespacePartsIndexedByPosition;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @return string
     */
    private function getNamespaceOfFile(File $file, int $position): string
    {
        $position = $file->findNext([T_STRING], $position);
        $token = $file->getTokens()[$position];
        $namespace = '';
        do {
            $namespace .= $token['content'];
            $position++;
            $token = $file->getTokens()[$position];
        } while ($token['code'] !== T_SEMICOLON);

        return $namespace;
    }

    /**
     * @param string $name
     * @param string $needle
     * @return bool
     */
    private function stringEndsWith(string $name, string $needle): bool
    {
        return substr($name, -strlen($needle)) === $needle;
    }
}
