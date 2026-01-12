<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Grid\UploadedFileGridFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Form\Admin\UploadedFile\UploadedFileFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormDataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_FILES)]
class UploadedFileController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormDataFactory $uploadedFileFormDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Grid\UploadedFileGridFactory $uploadedFileGridFactory
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly UploadedFileFormDataFactory $uploadedFileFormDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly UploadedFileGridFactory $uploadedFileGridFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/uploaded-file/list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $grid = $this->uploadedFileGridFactory->createWithSearch($quickSearchData);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/uploadedFile/list.html.twig', [
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
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $uploadedFile = $this->uploadedFileFacade->getById($id);
        $uploadedFileFormData = $this->uploadedFileFormDataFactory->createFromUploadedFile($uploadedFile);

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

        $deleteUrl = $this->generateUrl('admin_uploadedfile_delete', [
            'id' => $uploadedFile->getId(),
            RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute('admin_uploadedfile_delete'),
        ]);

        return $this->render('@ShopsysAdministration/content/uploadedFile/edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $uploadedFile,
            'deleteUrl' => $deleteUrl,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/uploaded-file/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $uploadedFileFormData = $this->uploadedFileFormDataFactory->create();

        $form = $this->createForm(UploadedFileFormType::class, $uploadedFileFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData $uploadedFileFormData */
            $uploadedFileFormData = $form->getData();

            $uploadedFiles = $this->uploadedFileFacade->create($uploadedFileFormData);

            $this->addSuccessFlashTwig(sprintf('%s<br /><br />%s', t('Files uploaded:'), implode(
                '<br />',
                array_map(
                    fn (UploadedFile $uploadedFile) => sprintf(
                        '<a href="%s">%s</a>',
                        $this->generateUrl('admin_uploadedfile_edit', ['id' => $uploadedFile->getId()]),
                        $uploadedFile->getNameWithExtension(),
                    ),
                    $uploadedFiles,
                ),
            )));

            return $this->redirectToRoute('admin_uploadedfile_list');
        }

        return $this->render('@ShopsysAdministration/content/uploadedFile/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/uploaded-file/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $uploadedFile = $this->uploadedFileFacade->getById($id);
            $this->uploadedFileFacade->deleteFile($uploadedFile);

            $this->addSuccessFlashTwig(
                t('File <strong>{{ fileName }}</strong> deleted'),
                [
                    'fileName' => $uploadedFile->getNameWithExtension(),
                ],
            );
        } catch (FileNotFoundException $ex) {
            $this->addErrorFlash(t('Selected file doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_uploadedfile_list');
    }
}
