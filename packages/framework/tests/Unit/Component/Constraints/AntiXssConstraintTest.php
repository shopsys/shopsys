<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Constraints;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Constraints\AntiXss;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CleanDataDto
{
    #[AntiXss]
    public string $name = 'John Doe';

    #[AntiXss]
    public string $description = 'This is a clean description';

    public string $unvalidatedField = '<script>anything</script>';
}

class XssDataDto
{
    #[AntiXss]
    public string $name = 'John Doe';

    #[AntiXss]
    public string $description = '<script>alert("XSS")</script>';

    public string $unvalidatedField = '<script>anything</script>';
}

#[AntiXss(excludedFields: [])]
class ClassLevelXssDto
{
    public string $name = 'John Doe';

    public string $bio = '<img src=x onerror=alert("XSS")>';

    public string $email = 'john@example.com';
}

#[AntiXss(excludedFields: ['bio'])]
class MixedConstraintsDto
{
    public string $name = '<script>alert("name")</script>';

    public string $bio = '<script>alert("bio")</script>'; // Excluded by class constraint

    public string $comment = '<img src=x onerror=alert("comment")>';
}

#[AntiXss(excludedFields: ['password', 'confirmPassword'])]
class FormDto
{
    public string $username = 'john_doe';

    public string $email = 'john@example.com';

    public string $password = '<script>alert("password")</script>'; // Excluded

    public string $confirmPassword = '<script>alert("confirm")</script>'; // Excluded

    public string $firstName = 'John';

    public string $lastName = '<iframe src="evil.com"></iframe>'; // Should trigger violation

    /**
     * @var array<string, string>
     */
    public array $preferences = [
        'theme' => 'dark',
        'customCss' => '<style>body { background: url("javascript:alert(1)") }</style>', // Should trigger violation
    ];
}

#[AntiXss(excludedFields: ['allowedField'])]
class ExplicitExclusionDto
{
    public string $allowedField = '<script>alert("allowed")</script>'; // Should be excluded

    public string $validatedField = '<script>alert("validated")</script>'; // Should trigger validation
}

class CustomMessageDto
{
    /**
     * @var string[]
     */
    #[AntiXss(
        excludedFields: ['allowedHtml', 'richContent'],
        message: 'Custom XSS error message',
    )]
    public array $data = [
        'title' => 'Clean title',
        'content' => '<script>alert("content")</script>',
        'allowedHtml' => '<div onclick="alert(1)">Allowed</div>',
        'richContent' => '<script>document.write("allowed")</script>',
    ];
}

class AntiXssConstraintTest extends TestCase
{
    private ValidatorInterface $validator;

    #[Override]
    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testPropertyLevelConstraintWithCleanData(): void
    {
        $violations = $this->validator->validate(new CleanDataDto());
        $this->assertCount(0, $violations);
    }

    public function testPropertyLevelConstraintWithXssContent(): void
    {
        $violations = $this->validator->validate(new XssDataDto());
        $this->assertCount(1, $violations);
        $this->assertEquals('description', $violations[0]->getPropertyPath());
    }

    public function testClassLevelConstraint(): void
    {
        $violations = $this->validator->validate(new ClassLevelXssDto());
        $this->assertCount(1, $violations);
        $this->assertEquals('bio', $violations[0]->getPropertyPath());
    }

    public function testMixedPropertyAndClassLevelConstraints(): void
    {
        $violations = $this->validator->validate(new MixedConstraintsDto());
        $this->assertCount(2, $violations);

        $paths = array_map(fn ($v) => $v->getPropertyPath(), iterator_to_array($violations));
        $this->assertContains('name', $paths);
        $this->assertContains('comment', $paths);
        $this->assertNotContains('bio', $paths); // Should be excluded
    }

    public function testFormDtoWithConstraints(): void
    {
        $violations = $this->validator->validate(new FormDto());
        $this->assertCount(2, $violations);

        $paths = array_map(fn ($v) => $v->getPropertyPath(), iterator_to_array($violations));
        $this->assertContains('lastName', $paths);
        $this->assertContains('preferences.customCss', $paths);
    }

    public function testExplicitFieldExclusions(): void
    {
        $violations = $this->validator->validate(new ExplicitExclusionDto());
        $this->assertCount(1, $violations);
        $this->assertEquals('validatedField', $violations[0]->getPropertyPath());
    }

    public function testCustomConstraintParameters(): void
    {
        $violations = $this->validator->validate(new CustomMessageDto());
        $this->assertCount(1, $violations);
        $this->assertEquals('data.content', $violations[0]->getPropertyPath());
        $this->assertEquals('Custom XSS error message', $violations[0]->getMessage());
    }
}
