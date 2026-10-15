<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\Validation;

use Override;
use Shopsys\AdministrationBundle\Component\ContentEditor\Form\ContentDocumentType;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentDocumentValidValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentDocumentValid) {
            throw new UnexpectedTypeException($constraint, ContentDocumentValid::class);
        }

        if (!$value instanceof ContentDocument) {
            return;
        }

        $form = $this->formFactory->createNamed('content', ContentDocumentType::class, null, ['variables' => $constraint->variables]);
        $form->submit(['blocks' => $value->toArray()['blocks']]);

        if ($form->isValid()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ count }}', (string)$this->countInvalidBlocks($form->get('blocks')))
            ->addViolation();
    }

    protected function countInvalidBlocks(FormInterface $blocks): int
    {
        $count = 0;

        foreach ($blocks as $block) {
            if ($block->has('data') && count($block->get('data')->getErrors(true)) > 0) {
                $count++;
            }

            if (!$block->has('children')) {
                continue;
            }

            foreach ($block->get('children') as $slot) {
                $count += $this->countInvalidBlocks($slot);
            }
        }

        return $count;
    }
}
