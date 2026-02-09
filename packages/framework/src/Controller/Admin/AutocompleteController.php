<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Autocomplete\AutocompleteFavoriteFormType;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteDataFactory;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[ForRole(AdminRoleConstant::ROLE_AUTOCOMPLETE)]
class AutocompleteController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly AutocompleteFavoriteDataFactory $autocompleteFavoriteDataFactory,
        protected readonly AutocompleteFavoriteFacade $autocompleteFavoriteFacade,
    ) {
    }

    #[Route(path: '/autocomplete/setting/')]
    #[CanEdit(methods: ['POST'])]
    #[CanView(methods: ['GET'])]
    public function settingAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $autocompleteFavoriteData = $this->autocompleteFavoriteDataFactory->createForDomain($domainId);

        $form = $this->createForm(AutocompleteFavoriteFormType::class, $autocompleteFavoriteData, [
            'domain_id' => $domainId,
            'locale' => $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->autocompleteFavoriteFacade->saveAllForDomain($domainId, $autocompleteFavoriteData);

            $this->addSuccessFlash(
                t('Autocomplete favorites successfully saved for the selected domain.'),
            );

            return $this->redirectToRoute('admin_autocomplete_setting');
        }

        return $this->render('@ShopsysAdministration/content/autocomplete/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
