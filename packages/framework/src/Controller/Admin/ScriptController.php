<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Form\Admin\Script\GoogleAnalyticsScriptFormType;
use Shopsys\FrameworkBundle\Form\Admin\Script\ScriptFormType;
use Shopsys\FrameworkBundle\Model\Script\Exception\ScriptNotFoundException;
use Shopsys\FrameworkBundle\Model\Script\Script;
use Shopsys\FrameworkBundle\Model\Script\ScriptDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ScriptController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptFacade
     */
    protected $scriptFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptDataFactoryInterface
     */
    protected $scriptDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptDataFactoryInterface $scriptDataFactory
     */
    public function __construct(
        ScriptFacade $scriptFacade,
        GridFactory $gridFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        ScriptDataFactoryInterface $scriptDataFactory
    ) {
        $this->scriptFacade = $scriptFacade;
        $this->gridFactory = $gridFactory;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->scriptDataFactory = $scriptDataFactory;
    }

    /**
     * @Route("/script/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $scriptData = $this->scriptDataFactory->create();
        $form = $this->createForm(ScriptFormType::class, $scriptData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $script = $this->scriptFacade->create($scriptData);

            $this
                ->addSuccessFlashTwig(
                    t('Script <a href="{{ url }}"><strong>{{ name }}</strong></a> created'),
                    [
                        'name' => $script->getName(),
                        'url' => $this->generateUrl('admin_script_edit', ['scriptId' => $script->getId()]),
                    ]
                );

            return $this->redirectToRoute('admin_script_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Script/new.html.twig', [
            'form' => $form->createView(),
            'scriptVariables' => $this->getOrderSentPageScriptVariableLabelsIndexedByVariables(),
        ]);
    }

    /**
     * @Route("/script/edit/{scriptId}")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $scriptId
     */
    public function editAction(Request $request, $scriptId)
    {
        $script = $this->scriptFacade->getById($scriptId);
        $scriptData = $this->scriptDataFactory->createFromScript($script);

        $form = $this->createForm(ScriptFormType::class, $scriptData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $script = $this->scriptFacade->edit($scriptId, $scriptData);

            $this
                ->addSuccessFlashTwig(
                    t('Script <a href="{{ url }}"><strong>{{ name }}</strong></a> modified'),
                    [
                        'name' => $script->getName(),
                        'url' => $this->generateUrl('admin_script_edit', ['scriptId' => $scriptId]),
                    ]
                );
            return $this->redirectToRoute('admin_script_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Script/edit.html.twig', [
            'script' => $script,
            'form' => $form->createView(),
            'scriptVariables' => $this->getOrderSentPageScriptVariableLabelsIndexedByVariables(),
        ]);
    }

    /**
     * @Route("/script/list/")
     */
    public function listAction()
    {
        $dataSource = new QueryBuilderDataSource($this->scriptFacade->getAllQueryBuilder(), 's.id');

        $grid = $this->gridFactory->create('scriptsList', $dataSource);

        $grid->addColumn('name', 's.name', t('Script name'));
        $grid->addColumn('placement', 's.placement', t('Location'));
        $grid->addEditActionColumn('admin_script_edit', ['scriptId' => 's.id']);
        $grid->addDeleteActionColumn('admin_script_delete', ['scriptId' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this script?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Script/listGrid.html.twig', [
            'PLACEMENT_ORDER_SENT_PAGE' => Script::PLACEMENT_ORDER_SENT_PAGE,
            'PLACEMENT_ALL_PAGES' => Script::PLACEMENT_ALL_PAGES,
            'PLACEMENT_ALL_PAGES_AFTER_CONTENT' => Script::PLACEMENT_ALL_PAGES_AFTER_CONTENT,
        ]);

        return $this->render('@ShopsysFramework/Admin/Content/Script/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/script/delete/{scriptId}")
     * @param int $scriptId
     */
    public function deleteAction($scriptId)
    {
        try {
            $script = $this->scriptFacade->getById($scriptId);

            $this->scriptFacade->delete($scriptId);

            $this->addSuccessFlashTwig(
                t('Script <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $script->getName(),
                ]
            );
        } catch (ScriptNotFoundException $ex) {
            $this->addErrorFlash(t('Selected script doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_script_list');
    }

    /**
     * @Route("/script/google-analytics/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function googleAnalyticsAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $formData = ['trackingId' => $this->scriptFacade->getGoogleAnalyticsTrackingId($domainId)];

        $form = $this->createForm(GoogleAnalyticsScriptFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->scriptFacade->setGoogleAnalyticsTrackingId($form->getData()['trackingId'], $domainId);
            $this->addSuccessFlashTwig(t('Google script code set'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Script/googleAnalytics.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return string[]
     */
    protected function getOrderSentPageScriptVariableLabelsIndexedByVariables()
    {
        return [
            ScriptFacade::VARIABLE_NUMBER => t('Order number'),
            ScriptFacade::VARIABLE_TOTAL_PRICE => t('Total order price including VAT'),
        ];
    }
}
