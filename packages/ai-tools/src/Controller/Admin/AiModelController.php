<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Controller\Admin;

use Exception;
use OpenAI\Client;
use Shopsys\AiToolsBundle\Form\Admin\AiModel\AiModelFormType;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiToDatabaseDataMapper;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelRepository;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiModelController extends AdminBaseController
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelRepository $aiModelRepository
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade $aiModelFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiToDatabaseDataMapper $aiModelApiToDatabaseDataMapper
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelDataFactory $aiModelDataFactory
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
    ) {
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid(): Grid
    {
        $queryBuilder = $this->aiModelFacade->getAllAiModelsQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'm.id');

        $gridId = 'ai_models_grid';
        $grid = $this->gridFactory->create($gridId, $dataSource);

        $grid->addColumn('name', 'm.name', t('Name'));
        $grid->addColumn('description', 'm.description', t('Description'));
        $grid->addColumn('isActive', 'm.isActive', t('Is Active'));
        $grid->addColumn('isDeprecated', 'm.isDeprecated', t('Is Deprecated'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('shopsys_aitools_admin_aimodel_edit', ['id' => 'm.id']);
        $grid->addDeleteActionColumn('shopsys_aitools_admin_aimodel_deleteconfirm', ['id' => 'm.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAiTools/Admin/Content/AiModel/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/ai-models/deleteconfirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this ai model?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'shopsys_aitools_admin_aimodel_delete', $id);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/ai-models/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): RedirectResponse
    {
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

        return $this->redirectToRoute('shopsys_aitools_admin_aimodel_list');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/ai-models/list')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysAiTools/Admin/Content/AiModel/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/ai-models/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response|RedirectResponse
    {
        $aiModel = $this->aiModelFacade->getById($id);
        $aiModelData = $this->aiModelDataFactory->createFromAiModel($aiModel);

        $form = $this->createForm(AiModelFormType::class, $aiModelData, [
            'aiModel' => $aiModel,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aiModel = $this->aiModelFacade->edit(
                $id,
                $aiModelData,
            );

            $this->addSuccessFlashTwig(
                t('AI model <strong>{{ name }}</strong> updated'),
                [
                    'name' => $aiModel->getName(),
                ],
            );

            return $this->redirectToRoute('shopsys_aitools_admin_aimodel_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAiTools/Admin/Content/AiModel/edit.html.twig', [
            'form' => $form->createView(),
            'aiModel' => $aiModel,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

        return $this->redirectToRoute('shopsys_aitools_admin_aimodel_list');
    }
}
