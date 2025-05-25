<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Exception;
use OpenAI\Client;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Form\Admin\AiModel\AiModelFormType;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelApiToDatabaseDataMapper;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelDataFactory;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelFacade;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AiModelController extends AdminBaseController
{

    /**
     * @param AiModelRepository $aiModelRepository
     * @param AiModelFacade $aiModelFacade
     * @param GridFactory $gridFactory
     * @param HttpClientInterface $httpClient
     * @param AiModelApiToDatabaseDataMapper $aiModelApiToDatabaseDataMapper
     * @param ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param AiModelDataFactory $aiModelDataFactory
     * @param \OpenAI\Client $client
     */
    public function __construct(
        protected readonly AiModelRepository $aiModelRepository,
        protected readonly AiModelFacade $aiModelFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly HttpClientInterface $httpClient,
        protected readonly AiModelApiToDatabaseDataMapper $aiModelApiToDatabaseDataMapper,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AiModelDataFactory $aiModelDataFactory,
        protected readonly Client $client,
    )
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     */
    public function getGrid(): Grid {
        $queryBuilder = $this->aiModelFacade->getAllAiModelsQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'm.id');

        $gridId = 'ai_models_grid';
        $grid = $this->gridFactory->create($gridId, $dataSource);

        $grid->addColumn('name', 'm.name', t('Name'));
        $grid->addColumn('description', 'm.description', t('Description'));
        $grid->addColumn('isActive', 'm.isActive', t('Is Active'));
        $grid->addColumn('isDeprecated', 'm.isDeprecated', t('Is Deprecated'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_aimodel_edit', ['id' => 'm.id']);
        $grid->addDeleteActionColumn('admin_aimodel_deleteconfirm', ['id' => 'm.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/AiModel/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param int $id
     * @return Response
     */
    #[Route(path: '/ai-models/deleteconfirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response {
        $message = t('Do you really want to remove this ai model?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_aimodel_delete', $id);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/ai-models/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): RedirectResponse {
        try {
            $this->aiModelFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('AI model <strong>{{ id }}</strong> deleted'),
                [
                    'id' => $id,
                ],
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected AI model doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_aimodel_list');
    }

    /**
     * @return Response
     */
    #[Route(path: '/ai-models/list')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/AiModel/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    #[Route(path: '/ai-models/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response|RedirectResponse
    {
        $aiModel = $this->aiModelFacade->getModelById($id);
        $aiModelData = $this->aiModelDataFactory->createFromAiModel($aiModel);

        $form = $this->createForm(AiModelFormType::class, $aiModelData, [
            'aiModel' => $aiModel,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aiModel = $this->aiModelFacade->edit(
                $id,
                $aiModelData
            );

            $this->addSuccessFlashTwig(
                t('AI model <strong>{{ name }}</strong> updated'),
                [
                    'name' => $aiModel->getName(),
                ],
            );

            return $this->redirectToRoute('admin_aimodel_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/AiModel/edit.html.twig', [
            'form' => $form->createView(),
            'aiModel' => $aiModel,
        ]);
    }

    /**
     * @return RedirectResponse|Response
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    #[Route(path: '/ai-models/fetch')]
    public function fetchAction(): RedirectResponse|Response
    {
        try {
            $response = $this->client->models()->list();

            $data = $response->toArray();
            $currentModels = $this->aiModelFacade->getAllAiModels();
            $aiModels = $this->aiModelApiToDatabaseDataMapper->mapApiDataToDatabaseData($data, $currentModels);
            $this->aiModelFacade->saveAiModels($aiModels);
        } catch (Exception $e) {
            $this->addErrorFlashTwig(t('Failed to fetch data from OpenAI API: %message%', ['%message%' => $e->getMessage()]));
        }

        return $this->redirect($this->generateUrl('admin_aimodel_list'));
    }
}
