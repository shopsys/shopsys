<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Form\Admin\UploadedFile\UploadedFileFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormDataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadedFileController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade $uploadedFileAdminListFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormDataFactory $uploadedFileFormDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileAdminListFacade $uploadedFileAdminListFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly UploadedFileFormDataFactory $uploadedFileFormDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/uploaded-file/')]
    public function listAction(Request $request): Response
    {
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);

        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->uploadedFileAdminListFacade->getQueryBuilderByQuickSearchData($quickSearchData);
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'u.id',
            function ($row) {
                $row['filename'] = sprintf('%s.%s', $row['u']['name'], $row['u']['extension']);

                return $row;
            },
        );

        $grid = $this->gridFactory->create('uploadedFiles', $dataSource);
        $grid->enablePaging();

        $grid->addColumn('filename', 'filename', t('Filename'));
        $grid->addColumn('translatedName', 'ut.name', t('Name'), true);
        $grid->addColumn('extension', 'u.extension', t('Ext.'), true);

        $grid->addEditActionColumn('admin_uploadedfile_edit', ['id' => 'u.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/UploadedFile/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysFramework/Admin/Content/UploadedFile/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/uploaded-file/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $uploadedFile = $this->uploadedFileFacade->getById($id);
        $uploadedFileFormData = $this->uploadedFileFormDataFactory->create($uploadedFile);

        $form = $this->createForm(UploadedFileFormType::class, $uploadedFileFormData, [
            'uploaded_file' => $uploadedFile,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploadedFileFacade->edit($uploadedFile, $uploadedFileFormData);

            $this->addSuccessFlashTwig(
                t('Uploaded file <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $uploadedFile->getNameWithExtension(),
                    'url' => $this->generateUrl('admin_uploadedfile_edit', ['id' => $uploadedFile->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_uploadedfile_list');
        }

        $this->breadcrumbOverrider->overrideLastItem(
            sprintf('%s - %s', t('Editing file'), $uploadedFile->getNameWithExtension()),
        );

        return $this->render('@ShopsysFramework/Admin/Content/UploadedFile/edit.html.twig', [
            'form' => $form->createView(),
            'uploadedFile' => $uploadedFile,
        ]);
    }
}
