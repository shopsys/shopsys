<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Component\UploadedFile\Grid\FilePickerGridFactory;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FilePickerController extends AdminBaseController
{
    public function __construct(
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly FilePickerGridFactory $filePickerGridFactory,
    ) {
    }

    #[Route(path: '/file-picker/pick-multiple/{jsInstanceId}')]
    #[RequireRole(SystemRole::ADMIN)]
    public function pickMultipleAction(
        Request $request,
        int|string $jsInstanceId,
    ): Response {
        return $this->getPickerResponse($request, $jsInstanceId, true);
    }

    #[Route(path: '/file-picker/pick-single/{jsInstanceId}/', defaults: ['jsInstanceId' => '__js_instance_id__'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function pickSingleAction(
        Request $request,
        string $jsInstanceId,
    ): Response {
        return $this->getPickerResponse($request, $jsInstanceId, false);
    }

    protected function getPickerResponse(
        Request $request,
        string $jsInstanceId,
        bool $isMultiple,
    ): Response {
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $grid = $this->filePickerGridFactory->createWithSearch($jsInstanceId, $quickSearchData, $isMultiple);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $viewParameters['gridView'] = $grid->createView();
        $viewParameters['quickSearchForm'] = $quickSearchForm->createView();

        return $this->render('@ShopsysAdministration/content/filePicker/list.html.twig', $viewParameters);
    }
}
