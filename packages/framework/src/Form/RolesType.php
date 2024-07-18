<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesType extends AbstractType
{
    /**
     * @var array<array<string, string>>
     */
    private array $rolesChoices;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\Roles $roles
     */
    public function __construct(Roles $roles)
    {
        $rolesGrid = $roles->getAvailableAdministratorRolesGrid();

        foreach ($rolesGrid as &$rolesRow) {
            $rolesRow = array_flip($rolesRow);
        }

        $this->rolesChoices = $rolesGrid;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'label' => false,
            'choices' => $this->rolesChoices,
        ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
