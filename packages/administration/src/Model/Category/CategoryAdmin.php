<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Category;

use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategoryAdmin extends AbstractAdmin
{
    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class);
    }
}
