<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Controller\Admin;

use Shopsys\AiToolsBundle\Component\Ai\Application\AiVectorStoreFacade;
use Shopsys\AiToolsBundle\Form\Admin\VectorStore\VectorStoreFormType;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreDataFactory;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreRepository;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Grid\ActionColumn;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class VectorStoreController extends AdminBaseController
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade $vectorStoreFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreDataFactory $vectorStoreDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreRepository $vectorStoreRepository
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\AiVectorStoreFacade $aiVectorStoreFacade
     */
    public function __construct(
        protected readonly VectorStoreFacade $vectorStoreFacade,
        protected readonly VectorStoreDataFactory $vectorStoreDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly VectorStoreRepository $vectorStoreRepository,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AiVectorStoreFacade $aiVectorStoreFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/new/')]
    public function newAction(Request $request): Response
    {
        $vectorStoreData = $this->vectorStoreDataFactory->create();

        $form = $this->createForm(VectorStoreFormType::class, $vectorStoreData, [
            'vectorStore' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vectorStore = $this->vectorStoreFacade->create($vectorStoreData);

            $this
                ->addSuccessFlashTwig(
                    t('Vector store <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $vectorStore->getName(),
                        'url' => $this->generateUrl('shopsys_aitools_admin_vectorstore_edit', ['id' => $vectorStore->getId()]),
                    ],
                );

            return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAiTools/Admin/Content/VectorStore/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $vectorStore = $this->vectorStoreFacade->getById($id);
        $vectorStoreData = $this->vectorStoreDataFactory->createFromVectorStore($vectorStore);

        $form = $this->createForm(VectorStoreFormType::class, $vectorStoreData, [
            'vectorStore' => $vectorStore,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vectorStore = $this->vectorStoreFacade->edit($id, $vectorStoreData);

            $this
                ->addSuccessFlashTwig(
                    t('Vector store <strong><a href="{{ url }}">{{ name }}</a></strong> edited'),
                    [
                        'name' => $vectorStore->getName(),
                        'url' => $this->generateUrl('shopsys_aitools_admin_vectorstore_edit', ['id' => $vectorStore->getId()]),
                    ],
                );

            return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAiTools/Admin/Content/VectorStore/edit.html.twig', [
            'form' => $form->createView(),
            'vectorStore' => $vectorStore,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/load/')]
    public function loadAction(): Response
    {
        $vectorStoresResponse = $this->aiVectorStoreFacade->getAllVectorStoreResponses();

        if (count($vectorStoresResponse) === 0) {
            $this->addInfoFlashTwig(
                t('No vector store found in API.'),
            );
        }

        $connected = 0;

        /** @var array{externalId: string, name: string} $vectorStoreResponse */
        foreach ($vectorStoresResponse as $vectorStoreResponse) {
            $vectorStore = $this->vectorStoreFacade->findByExternalId($vectorStoreResponse['externalId']);

            if ($vectorStore) {
                continue;
            }

            $vectorStoreData = $this->vectorStoreDataFactory->create();
            $vectorStoreData->externalId = $vectorStoreResponse['externalId'];
            $vectorStoreData->name = $vectorStoreResponse['name'];
            $this->vectorStoreFacade->create($vectorStoreData);
            $connected++;
        }

        if ($connected !== 0) {
            $this
                ->addSuccessFlashTwig(
                    t('Connected {{ count }} vector stores'),
                    [
                        'count' => $connected,
                    ],
                );
        }

        return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/connect/{id}', requirements: ['id' => '\d+'])]
    public function connectAction(int $id): Response
    {
        try {
            $vectorStore = $this->vectorStoreFacade->getById($id);

            if ($vectorStore->getExternalId()) {
                $this->addInfoFlashTwig(
                    t('Vector store <strong>{{ name }}</strong> is already connected.'),
                    [
                        'name' => $vectorStore->getName(),
                    ],
                );

                return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
            }

            $externalId = $this->aiVectorStoreFacade->createVectorStore($vectorStore);

            if ($externalId === null) {
                $this->addErrorFlashTwig(
                    t('Vector store <strong>{{ name }}</strong> connection failed.'),
                    [
                        'name' => $vectorStore->getName(),
                    ],
                );

                return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
            }

            $vectorStoreData = $this->vectorStoreDataFactory->createFromVectorStore($vectorStore);
            $vectorStoreData->externalId = $externalId;
            $this->vectorStoreFacade->edit($id, $vectorStoreData);

            $this->addSuccessFlashTwig(
                t('Vector store <strong>{{ name }}</strong> was successfully connected.'),
                [
                    'name' => $vectorStore->getName(),
                ],
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected vector store doesn\'t exist.'));
        }

        return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $vectorStore = $this->vectorStoreFacade->getById($id);

            if ($vectorStore->getExternalId()) {
                $this->aiVectorStoreFacade->deleteVectorStore($vectorStore);
            }

            $fullName = $vectorStore->getName();

            $this->vectorStoreFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Vector store <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected vector store doesn\'t exist.'));
        }

        return $this->redirectToRoute('shopsys_aitools_admin_vectorstore_list');
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this vector stores?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'shopsys_aitools_admin_vectorstore_delete', $id);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/vector-store/list/')]
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('@ShopsysAiTools/Admin/Content/VectorStore/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid(): Grid
    {
        $queryBuilder = $this->vectorStoreRepository->getAllQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'vs.id');

        $gridId = 'vector-stores-grid';
        $grid = $this->gridFactory->create($gridId, $dataSource);
        $grid->setDefaultOrder('id');

        $grid->addColumn('name', 'vs.name', t('Name'));
        $grid->addColumn('externalId', 'vs.externalId', t('External ID'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');

        $grid->addActionColumn(ActionColumn::TYPE_RESET_PASSWORD, t('Connect vector store in API'), 'shopsys_aitools_admin_vectorstore_connect', ['id' => 'vs.id']);

        $grid->addEditActionColumn('shopsys_aitools_admin_vectorstore_edit', ['id' => 'vs.id']);
        $grid->addDeleteActionColumn('shopsys_aitools_admin_vectorstore_deleteconfirm', ['id' => 'vs.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAiTools/Admin/Content/VectorStore/listGrid.html.twig');

        return $grid;
    }
}
