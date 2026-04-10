<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Phpstan;

use Override;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Shopsys\McpBundle\Phpstan\McpEntityExposureAttributeRule;
use function usort;

/**
 * @extends \PHPStan\Testing\RuleTestCase<\Shopsys\McpBundle\Phpstan\McpEntityExposureAttributeRule>
 */
class McpEntityExposureAttributeRuleTest extends RuleTestCase
{
    private McpEntityExposureAttributeRule $rule;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new McpEntityExposureAttributeRule([]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return $this->rule;
    }

    public function testReportsInvalidMcpExposureConfiguration(): void
    {
        $fixtureFile = __DIR__ . '/data/InvalidMcpEntityExposureEntities.php';
        require_once $fixtureFile;

        $errors = $this->gatherAnalyserErrors([$fixtureFile]);
        $actualErrors = array_map(
            static fn (Error $error): array => [$error->getIdentifier(), $error->getLine()],
            $errors,
        );
        usort(
            $actualErrors,
            static fn (array $firstError, array $secondError): int => [$firstError[1], $firstError[0]] <=> [$secondError[1], $secondError[0]],
        );

        $this->assertSame(
            [
                [McpEntityExposureAttributeRule::IDENTIFIER_COLUMN_EXPOSURE, 11],
                [McpEntityExposureAttributeRule::IDENTIFIER_COLUMN_FIELD_NAME, 11],
                [McpEntityExposureAttributeRule::IDENTIFIER_COLUMN_PROPERTY_FIELD_NAME, 11],
                [McpEntityExposureAttributeRule::IDENTIFIER_COLUMN_UNKNOWN_FIELD, 29],
                [McpEntityExposureAttributeRule::IDENTIFIER_ENTITY_EXPOSURE, 44],
                [McpEntityExposureAttributeRule::IDENTIFIER_COLUMN_DUPLICATE_FIELD_NAME, 52],
            ],
            $actualErrors,
        );
    }

    public function testAcceptsValidMcpExposureConfiguration(): void
    {
        $fixtureFile = __DIR__ . '/data/ValidMcpEntityExposureEntity.php';
        require_once $fixtureFile;

        $errors = $this->gatherAnalyserErrors([$fixtureFile]);

        $this->assertSame([], $errors);
    }

    public function testSkipsFilesInTestsDirectoriesByDefault(): void
    {
        $fixtureFile = __DIR__ . '/data/InvalidMcpEntityExposureEntities.php';
        require_once $fixtureFile;
        $this->rule = new McpEntityExposureAttributeRule();

        $errors = $this->gatherAnalyserErrors([$fixtureFile]);

        $this->assertSame([], $errors);
    }
}
