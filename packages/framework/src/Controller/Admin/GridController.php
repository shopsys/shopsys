<?php

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
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\InlineEditFacade<array<string, mixed>>
     */
    protected $inlineEditFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade
     */
    protected $gridOrderingFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade $gridOrderingFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\InlineEditFacade<array<string, mixed>> $inlineEditFacade
     */
    public function __construct(
        GridOrderingFacade $gridOrderingFacade,
        InlineEditFacade $inlineEditFacade
    ) {
        $this->gridOrderingFacade = $gridOrderingFacade;
        $this->inlineEditFacade = $inlineEditFacade;
    }

    /**
     * @Route("/_grid/get-form/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFormAction(Request $request): JsonResponse
    {
        $renderedFormRow = $this->inlineEditFacade->getRenderedFormRow(
            $request->get('serviceName'),
            json_decode($request->get('rowId'))
        );

        return new JsonResponse($renderedFormRow);
    }

    /**
     * @Route("/_grid/save-form/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveFormAction(Request $request): JsonResponse
    {
        $responseData = [];
        $rowId = json_decode($request->get('rowId'));

        try {
            $rowId = $this->inlineEditFacade->saveFormData($request->get('serviceName'), $request, $rowId);

            $responseData['success'] = true;
            $responseData['rowHtml'] = $this->inlineEditFacade->getRenderedRowHtml(
                $request->get('serviceName'),
                $rowId
            );
        } catch (InvalidFormDataException $e) {
            $responseData['success'] = false;
            // reset array keys for array representation in JSON, otherwise it could be treated as an object
            $responseData['errors'] = array_values(array_unique($e->getFormErrors()));
        }

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/_grid/save-ordering/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveOrderingAction(Request $request): JsonResponse
    {
        $this->gridOrderingFacade->saveOrdering(
            $request->get('entityClass'),
            array_map('json_decode', $request->get('rowIds'))
        );
        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }
}
