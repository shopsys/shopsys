<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin\Administrator;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use App\Model\Security\Roles;
use Shopsys\AdminBundle\Component\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdministratorAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
    ) {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->with('Basic Information', ['class' => 'col-md-9'])
                ->add('username', TextType::class)
            ->add('realName')
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                'label' => 'Password confirmation',
                'type' => PasswordType::class,
                'required' => $this->isCurrentRoute('create'),
            ])
            ->end()
        ->with('Security', ['class' => 'col-md-3'])
            ->add('roleGroup', ChoiceFieldMaskType::class, [
                'choices' => $this->administratorRoleGroupFacade->getAll(),
                'required' => false,
                'placeholder' => 'Custom',
                'label' => t('Role Group'),
                'choice_label' => function (AdministratorRoleGroup $administratorRoleGroup) {
                    return $administratorRoleGroup->getName();
                },
                'map' => [
                    null => ['roles'],
                ]
            ])
            ->add('roles', ChoiceType::class ,[
                'choices' => Roles::getAvailableAdministratorRolesChoices(),
                'placeholder' => t('-- Select a role --'),
                'label' => t('Role'),
                'required' => false,
                'multiple' => true,
            ])
        ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('email');
    }


    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('email');
        $list->add('realName');

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
            ]
        ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->andWhere('o.id != 1');

        return $query;
    }

}