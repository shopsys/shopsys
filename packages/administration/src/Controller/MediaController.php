<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\Media\MediaCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Form\Admin\Media\MediaFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use SortDirection;

#[CrudController(Media::class)]
#[ForRole(AdminRoleConstant::ROLE_MEDIA)]
class MediaController extends AbstractCrudController
{
    public function __construct(
        protected readonly MediaFacade $mediaFacade,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setEntityNamePlural(t('Media'))
            ->setMenuSection(
                SideMenuBuilder::ROOT_CMS,
                null,
                ['after' => 'admin_crud_advert_list'],
            )
            ->registerHandler(MediaCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('thumbnail', [
                'label' => t('Image'),
                'property' => 'id',
                'sortable' => false,
                // the image URL is built from the entity, a grid row holds only scalar values
                'transform' => fn (int $mediaId): Media => $this->mediaFacade->getById($mediaId),
                'template' => '@ShopsysAdministration/content/media/grid/thumbnail.html.twig',
            ])
            ->add('title', [
                'label' => t('Title'),
            ])
            ->add('size', [
                'label' => t('Size (px)'),
                'property' => ['width', 'height'],
                'transform' => fn (array $size): string => $size['width'] === null ? '' : $size['width'] . ' × ' . $size['height'],
            ])
            ->add('createdAt', [
                'label' => t('Created at'),
            ]);

        $datagrid->setDefaultOrder('createdAt', SortDirection::Descending);
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
        $formConfigurator->useFormType(MediaFormType::class, [
            'media' => $entity,
        ]);
    }
}
