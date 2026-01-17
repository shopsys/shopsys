<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use GuzzleHttp\Exception\GuzzleException;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\LanguageConstant\LanguageConstantFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\LanguageConstant\Exception\LanguageConstantNotFoundException;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantDataFactory;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFacade;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_LANGUAGE_CONSTANTS)]
class LanguageConstantController extends AdminBaseController
{
    public function __construct(
        protected readonly LanguageConstantFacade $languageConstantFacade,
        protected readonly LanguageConstantDataFactory $languageConstantDataFactory,
        protected readonly LanguageConstantGridFactory $languageConstantGridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/constant/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData())->handleRequest($request);
        /** @var \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData */
        $quickSearchData = $quickSearchForm->getData();

        try {
            $grid = $this->languageConstantGridFactory->create($this->getSelectedLocale(), $quickSearchData->text);
        } catch (GuzzleException $exception) {
            $grid = null;
            $this->addErrorFlashTwig(t('Unable to load list of language constants'));
        }

        return $this->render('@ShopsysAdministration/content/languageConstant/list.html.twig', [
            'gridView' => $grid?->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/constant/edit/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request): Response
    {
        $key = $request->query->get('key');
        $namespace = $request->query->get('namespace', LanguageConstant::NAMESPACE_COMMON);
        $locale = $this->getSelectedLocale();
        $translation = $this->languageConstantFacade->getOriginalTranslationsByLocaleIndexedByKey($locale, $namespace)[$key] ?? null;

        if ($translation === null) {
            $this->addErrorFlashTwig(
                t('Language constant translation <strong>{{ name }}</strong> not found'),
                [
                    'name' => $key,
                ],
            );

            return $this->redirectToRoute('admin_languageconstant_list');
        }

        $constant = $this->languageConstantFacade->findByKey($key, $namespace);
        $constantData = $this->languageConstantDataFactory->createFromDataOrLanguageConstant($key, $locale, $translation, $constant, $namespace);

        $form = $this->createForm(LanguageConstantFormType::class, $constantData)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $constant = $this->languageConstantFacade->createOrEdit($form->getData(), $constant);

                $this->addSuccessFlashTwig(
                    t('Language constant translation <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $constant->getKey(),
                        'url' => $this->generateUrl('admin_languageconstant_edit', ['key' => $constant->getKey(), 'namespace' => $constant->getNamespace()]),
                    ],
                );
            } catch (LanguageConstantNotFoundException $exception) {
                $this->addErrorFlashTwig(
                    t('Language constant translation <strong>{{ name }}</strong> not found'),
                    [
                        'name' => $key,
                    ],
                );
            }

            $this->languageConstantFacade->generateAllNamespaceFiles($locale);

            return $this->redirectToRoute('admin_languageconstant_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/languageConstant/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/constant/delete/')]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(Request $request): Response
    {
        $key = $request->query->get('key');
        $namespace = $request->query->get('namespace', LanguageConstant::NAMESPACE_COMMON);
        $constant = $this->languageConstantFacade->findByKey($key, $namespace);

        if ($constant !== null) {
            try {
                $this->languageConstantFacade->delete($key, $this->getSelectedLocale(), $namespace);
                $this->languageConstantFacade->generateAllNamespaceFiles($this->getSelectedLocale());

                $this->addSuccessFlashTwig(
                    t('Language constant translation <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $constant->getKey(),
                    ],
                );
            } catch (LanguageConstantNotFoundException $exception) {
                $this->addErrorFlashTwig(
                    t('Language constant translation <strong>{{ name }}</strong> not found'),
                    [
                        'name' => $key,
                    ],
                );
            }
        }

        return $this->redirectToRoute('admin_languageconstant_list');
    }

    protected function getSelectedLocale(): string
    {
        return $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();
    }
}
