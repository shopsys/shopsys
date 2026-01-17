<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToFolderFailedException;
use Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\Domain\DomainFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_DOMAIN)]
class DomainController extends AdminBaseController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly DomainFacade $domainFacade,
        protected readonly ErrorExtractor $errorExtractor,
        protected readonly ArrayDataSourceFactory $arrayDataSourceFactory,
    ) {
    }

    #[RequireRole(SystemRole::ADMIN)]
    public function domainTabsAction(): Response
    {
        return $this->render('@ShopsysAdministration/partial/switch_domain.html.twig', [
            'domainConfigs' => $this->domain->getAdminEnabledDomains(),
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }

    #[Route(path: '/multidomain/select-domain/{id}', requirements: ['id' => '\d+'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function selectDomainAction(Request $request, int $id): Response
    {
        $this->adminDomainTabsFacade->setSelectedDomainId($id);

        $referer = $request->server->get('HTTP_REFERER');

        if ($referer === null) {
            return $this->redirectToRoute('admin_default_dashboard');
        }

        return $this->redirect($referer);
    }

    #[Route(path: '/domain/list')]
    #[CanView]
    public function listAction(): Response
    {
        $dataSource = $this->arrayDataSourceFactory->create($this->loadData(), 'id');

        $grid = $this->gridFactory->create('domainsList', $dataSource, SystemRole::ADMIN);

        $grid->addColumn('name', 'name', t('Domain name'));
        $grid->addColumn('locale', 'locale', t('Language'));
        $grid->addColumn('icon', 'icon', t('Icon'));
        $grid->addEditActionColumn('admin_domain_edit', ['id' => 'id']);

        $grid->setTheme('@ShopsysAdministration/content/domain/listGrid.html.twig');

        return $this->render('@ShopsysAdministration/content/domain/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/domain/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $domain = $this->domain->getDomainConfigById($id);

        $form = $this->createForm(DomainFormType::class, null, ['domain' => $domain]);
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
                        ['name' => $domain->getName()],
                    );
                }

                return $this->redirectToRoute('admin_domain_list');
            } catch (FileIsNotSupportedImageException $ex) {
                $this->addErrorFlash(t('File type not supported.'));
            } catch (MoveToFolderFailedException $ex) {
                $this->addErrorFlash(t('File upload failed, try again please.'));
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/domain/edit.html.twig', [
            'form' => $form->createView(),
            'domain' => $domain,
        ]);
    }

    public function localeTabsAction(): Response
    {
        $domainConfigs = [];

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            if (!isset($domainConfigs[$domainConfig->getLocale()])) {
                $domainConfigs[$domainConfig->getLocale()] = $domainConfig;
            }
        }

        return $this->render('@ShopsysAdministration/partial/switch_locale.html.twig', [
            'domainConfigs' => $domainConfigs,
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }

    /**
     * @return array<int ,array{id: int, name: string, locale: string, icon: string|null}>
     */
    protected function loadData(): array
    {
        $data = [];

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
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
