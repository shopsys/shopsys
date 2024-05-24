<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Article;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleAdmin extends AbstractAdmin
{
    use SortableAdminTrait;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     */
    public function __construct(
        protected readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
    ) {
        parent::__construct();
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name');
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('name', FieldDescriptionInterface::TYPE_URL, [
            'route' => [
                'name' => 'admin_new_article_edit',
                'identifier_parameter_name' => 'id',
            ],
        ]);

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
                'move' => [
                    'template' => '@RunroomSortableBehavior/sort_drag_drop.html.twig',
                ],
            ],
        ]);
    }
}
