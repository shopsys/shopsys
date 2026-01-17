<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\InlineEditFacade;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GridController extends AdminBaseController
{
    public function __construct(
        protected readonly GridOrderingFacade $gridOrderingFacade,
        protected readonly InlineEditFacade $inlineEditFacade,
    ) {
    }

    #[Route(path: '/_grid/get-form/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function getFormAction(Request $request): JsonResponse
    {
        $rowId = $request->request->has('rowId') ? json_decode($request->request->getString('rowId')) : null;

        $renderedFormRow = $this->inlineEditFacade->getRenderedFormRow(
            $request->request->getString('serviceName'),
            $rowId,
        );

        return new JsonResponse($renderedFormRow);
    }

    #[Route(path: '/_grid/save-form/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function saveFormAction(Request $request): JsonResponse
    {
        $responseData = [];
        $rowId = $request->request->has('rowId') ? json_decode($request->request->getString('rowId')) : null;
        $serviceName = $request->request->getString('serviceName');

        try {
            $rowId = $this->inlineEditFacade->saveFormData($serviceName, $request, $rowId);

            $responseData['success'] = true;
            $responseData['rowHtml'] = $this->inlineEditFacade->getRenderedRowHtml(
                $serviceName,
                $rowId,
            );
        } catch (InvalidFormDataException $e) {
            $responseData['success'] = false;
            // reset array keys for array representation in JSON, otherwise it could be treated as an object
            $responseData['errors'] = array_values(array_unique($e->getFormErrors()));
        }

        return new JsonResponse($responseData);
    }

    #[Route(path: '/_grid/save-ordering/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function saveOrderingAction(Request $request): JsonResponse
    {
        $this->gridOrderingFacade->saveOrdering(
            $request->request->getString('entityClass'),
            array_map('json_decode', $request->request->all('rowIds')),
        );
        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }
}
