<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\InlineEditFacade;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GridController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade $gridOrderingFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\InlineEditFacade $inlineEditFacade
     */
    public function __construct(
        protected readonly GridOrderingFacade $gridOrderingFacade,
        protected readonly InlineEditFacade $inlineEditFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/_grid/get-form/')]
    public function getFormAction(Request $request)
    {
        $rowId = $request->get('rowId') !== null ? json_decode($request->get('rowId')) : null;

        $renderedFormRow = $this->inlineEditFacade->getRenderedFormRow(
            $request->get('serviceName'),
            $rowId,
        );

        return new JsonResponse($renderedFormRow);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/_grid/save-form/')]
    public function saveFormAction(Request $request)
    {
        $responseData = [];
        $rowId = $request->get('rowId') !== null ? json_decode($request->get('rowId')) : null;

        try {
            $rowId = $this->inlineEditFacade->saveFormData($request->get('serviceName'), $request, $rowId);

            $responseData['success'] = true;
            $responseData['rowHtml'] = $this->inlineEditFacade->getRenderedRowHtml(
                $request->get('serviceName'),
                $rowId,
            );
        } catch (InvalidFormDataException $e) {
            $responseData['success'] = false;
            // reset array keys for array representation in JSON, otherwise it could be treated as an object
            $responseData['errors'] = array_values(array_unique($e->getFormErrors()));
        }

        return new JsonResponse($responseData);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/_grid/save-ordering/')]
    public function saveOrderingAction(Request $request)
    {
        $this->gridOrderingFacade->saveOrdering(
            $request->get('entityClass'),
            array_map('json_decode', $request->get('rowIds')),
        );
        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }
}
