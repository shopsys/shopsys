<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\UploadedFile\Grid\FilePickerGridFactory;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilePickerController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Grid\FilePickerGridFactory $filePickerGridFactory
     */
    public function __construct(
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly FilePickerGridFactory $filePickerGridFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string $jsInstanceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/file-picker/pick-multiple/{jsInstanceId}')]
    public function pickMultipleAction(
        Request $request,
        int|string $jsInstanceId,
    ): Response {
        return $this->getPickerResponse(
            $request,
            $jsInstanceId,
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $jsInstanceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getPickerResponse(
        Request $request,
        string $jsInstanceId,
    ): Response {
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $grid = $this->filePickerGridFactory->createWithSearch($jsInstanceId, $quickSearchData);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $viewParameters['gridView'] = $grid->createView();
        $viewParameters['quickSearchForm'] = $quickSearchForm->createView();

        return $this->render('@ShopsysFramework/Admin/Content/FilePicker/list.html.twig', $viewParameters);
    }
}
