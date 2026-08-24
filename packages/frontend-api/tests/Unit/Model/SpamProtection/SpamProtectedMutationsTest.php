<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\SpamProtection;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionMethod;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade;
use Symfony\Component\Yaml\Yaml;

class SpamProtectedMutationsTest extends TestCase
{
    private const string HONEY_POT_INPUT_OBJECT_NAME = 'HoneyPotInputObject';
    private const string PACKAGE_PATH = __DIR__ . '/../../../..';
    private const string ROOT_NAMESPACE = 'Shopsys\\FrontendApiBundle\\';

    public function testMutationsWithHoneyPotFieldCheckTheSubmissionForSpam(): void
    {
        $unprotectedAliases = array_values(array_diff(
            $this->getMutationAliasesWithHoneyPotField(),
            $this->getMutationAliasesCheckingSubmissionForSpam(),
        ));

        self::assertSame([], $unprotectedAliases, sprintf(
            'The input of these mutations inherits %s, but they never call %s::shouldDiscardSubmission(), so their honey pot field is only a decoration: %s',
            self::HONEY_POT_INPUT_OBJECT_NAME,
            FormSpamProtectionFacade::class,
            implode(', ', $unprotectedAliases),
        ));
    }

    public function testMutationsCheckingSubmissionForSpamHaveHoneyPotFieldInTheirInput(): void
    {
        $aliasesWithoutHoneyPotField = array_values(array_diff(
            $this->getMutationAliasesCheckingSubmissionForSpam(),
            $this->getMutationAliasesWithHoneyPotField(),
        ));

        self::assertSame([], $aliasesWithoutHoneyPotField, sprintf(
            'These mutations check the submission for spam, but their input does not inherit %s, so the honey pot never catches anything and only the rate limit is left: %s',
            self::HONEY_POT_INPUT_OBJECT_NAME,
            implode(', ', $aliasesWithoutHoneyPotField),
        ));
    }

    /**
     * Both lists above are collected by scanning files, so they would silently become empty if the scanned paths ever moved.
     */
    public function testTheContactFormIsFoundByBothScans(): void
    {
        self::assertContains('contactFormMutation', $this->getMutationAliasesWithHoneyPotField());
        self::assertContains('contactFormMutation', $this->getMutationAliasesCheckingSubmissionForSpam());
    }

    /**
     * @return string[]
     */
    private function getMutationAliasesWithHoneyPotField(): array
    {
        $inputNamesWithHoneyPotField = $this->getInputNamesWithHoneyPotField();
        $aliases = [];

        foreach ($this->getTypeConfigs() as $typeConfig) {
            foreach ($typeConfig['config']['fields'] ?? [] as $field) {
                $alias = is_array($field) ? $this->getMutationAlias($field) : null;

                if ($alias === null || array_intersect($this->getArgumentTypeNames($field), $inputNamesWithHoneyPotField) === []) {
                    continue;
                }

                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    /**
     * @return string[]
     */
    private function getMutationAliasesCheckingSubmissionForSpam(): array
    {
        // the reflection is built from the facade on purpose, so that renaming its method breaks this test instead of turning it into a no-op
        $spamCheckCall = (new ReflectionMethod(FormSpamProtectionFacade::class, 'shouldDiscardSubmission'))->getName() . '(';
        $aliases = [];

        foreach ($this->getMutationMethodsByAlias() as $alias => $reflectionMethod) {
            if (str_contains($this->getMethodBody($reflectionMethod), $spamCheckCall)) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    /**
     * @return string[]
     */
    private function getInputNamesWithHoneyPotField(): array
    {
        $inputNames = [];

        foreach ($this->getTypeConfigs() as $typeName => $typeConfig) {
            if (in_array(self::HONEY_POT_INPUT_OBJECT_NAME, $typeConfig['inherits'] ?? [], true)) {
                $inputNames[] = preg_replace('~Decorator$~', '', $typeName);
            }
        }

        return $inputNames;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getTypeConfigs(): array
    {
        $typeConfigs = [];

        foreach ($this->findFiles('/src/Resources/config/graphql-types', '.types.yaml') as $file) {
            $typeConfigs += Yaml::parseFile($file->getPathname());
        }

        return $typeConfigs;
    }

    /**
     * @return array<string, \ReflectionMethod>
     */
    private function getMutationMethodsByAlias(): array
    {
        $sourcePath = realpath(self::PACKAGE_PATH . '/src');
        $methodsByAlias = [];

        foreach ($this->findFiles('/src', 'Mutation.php') as $file) {
            $relativePath = substr($file->getRealPath(), strlen($sourcePath) + 1, -strlen('.php'));
            $className = self::ROOT_NAMESPACE . str_replace('/', '\\', $relativePath);

            if (!class_exists($className) || !is_subclass_of($className, AbstractMutation::class)) {
                continue;
            }

            foreach ($className::getAliases() as $alias => $methodName) {
                $methodsByAlias[$alias] = new ReflectionMethod($className, $methodName);
            }
        }

        return $methodsByAlias;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function getMutationAlias(array $field): ?string
    {
        if (preg_match('~mutation\(\'(?<alias>[^\']+)\'~', (string)($field['resolve'] ?? ''), $matches) !== 1) {
            return null;
        }

        return $matches['alias'];
    }

    /**
     * @param array<string, mixed> $field
     * @return string[]
     */
    private function getArgumentTypeNames(array $field): array
    {
        $typeNames = [];

        foreach ($field['args'] ?? [] as $argument) {
            $typeNames[] = trim($argument['type'], '[]!');
        }

        return $typeNames;
    }

    private function getMethodBody(ReflectionMethod $reflectionMethod): string
    {
        $lines = file($reflectionMethod->getFileName());

        return implode('', array_slice(
            $lines,
            $reflectionMethod->getStartLine(),
            $reflectionMethod->getEndLine() - $reflectionMethod->getStartLine(),
        ));
    }

    /**
     * @return \SplFileInfo[]
     */
    private function findFiles(string $relativeDirectory, string $suffix): array
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            self::PACKAGE_PATH . $relativeDirectory,
            RecursiveDirectoryIterator::SKIP_DOTS,
        );
        $files = [];

        /** @var \SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $suffix)) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
