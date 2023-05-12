<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToFolderFailedException;
use Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Form\Admin\Domain\DomainFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomainController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainFacade $domainFacade
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor $errorExtractor
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly DomainFacade $domainFacade,
        protected readonly ErrorExtractor $errorExtractor
    ) {
    }

    public function domainTabsAction()
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Domain/tabs.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }

    /**
     * @Route("/multidomain/select-domain/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $id
     */
    public function selectDomainAction(Request $request, $id)
    {
        $id = (int)$id;

        $this->adminDomainTabsFacade->setSelectedDomainId($id);

        $referer = $request->server->get('HTTP_REFERER');

        if ($referer === null) {
            return $this->redirectToRoute('admin_default_dashboard');
        }

        return $this->redirect($referer);
    }

    /**
     * @Route("/domain/list")
     */
    public function listAction()
    {
        $dataSource = new ArrayDataSource($this->loadData(), 'id');

        $grid = $this->gridFactory->create('domainsList', $dataSource);

        $grid->addColumn('name', 'name', t('Domain name'));
        $grid->addColumn('locale', 'locale', t('Language'));
        $grid->addColumn('icon', 'icon', t('Icon'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Domain/listGrid.html.twig');

        return $this->render('@ShopsysFramework/Admin/Content/Domain/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/domain/edit/{id}", requirements={"id" = "\d+"}, condition="request.isXmlHttpRequest()")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, $id)
    {
        $id = (int)$id;
        $domain = $this->domain->getDomainConfigById($id);

        $form = $this->createForm(DomainFormType::class, null, [
            'action' => $this->generateUrl('admin_domain_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData|null $iconData */
                $iconData = $form->getData()[DomainFormType::FIELD_ICON];
                $files = $iconData !== null ? $iconData->uploadedFiles : [];

                if (count($files) !== 0) {
                    $iconName = reset($files);

                    $this->domainFacade->editIcon($id, $iconName);

                    $this->addSuccessFlashTwig(
                        t('Domain <strong>{{ name }}</strong> modified. Try clearing your browser cache (CTRL+F5) if you can\'t see the new icon.'),
                        ['name' => $domain->getName()]
                    );
                }

                return new JsonResponse(['result' => 'valid']);
            } catch (FileIsNotSupportedImageException $ex) {
                $this->addErrorFlash(t('File type not supported.'));
            } catch (MoveToFolderFailedException $ex) {
                $this->addErrorFlash(t('File upload failed, try again please.'));
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse([
                'result' => 'invalid',
                'errors' => $this->errorExtractor->getAllErrorsAsArray($form, $this->getErrorMessages()),
            ]);
        }

        return $this->render('@ShopsysFramework/Admin/Content/Domain/edit.html.twig', [
            'form' => $form->createView(),
            'domain' => $domain,
        ]);
    }

    protected function loadData()
    {
        $data = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $data[] = [
                'id' => $domainConfig->getId(),
                'name' => $domainConfig->getName(),
                'locale' => $domainConfig->getLocale(),
                'icon' => null,
            ];
        }

        return $data;
    }
}
