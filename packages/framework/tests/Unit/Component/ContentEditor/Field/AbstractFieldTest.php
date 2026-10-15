<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AbstractFieldTest extends TestCase
{
    public function testRequiredFieldHasNotBlankConstraintFirst(): void
    {
        $length = new Length(max: 5);
        $field = TextField::create('title')->required()->constraints([$length]);

        $constraints = $field->getConstraints();

        $this->assertCount(2, $constraints);
        $this->assertInstanceOf(NotBlank::class, $constraints[0]);
        $this->assertSame($length, $constraints[1]);
    }

    public function testNotRequiredFieldHasNoNotBlankConstraint(): void
    {
        $length = new Length(max: 5);
        $field = TextField::create('title')->constraints([$length]);

        $this->assertSame([$length], $field->getConstraints());
    }
}
