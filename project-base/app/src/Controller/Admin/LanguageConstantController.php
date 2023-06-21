<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\LanguageConstant\LanguageConstantFormType;
use App\Model\LanguageConstant\Exception\LanguageConstantNotFoundException;
use App\Model\LanguageConstant\LanguageConstantDataFactory;
use App\Model\LanguageConstant\LanguageConstantFacade;
use App\Model\LanguageConstant\LanguageConstantGridFactory;
use GuzzleHttp\Exception\GuzzleException;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageConstantController extends AdminBaseController
{
    /**
     * @param \App\Model\LanguageConstant\LanguageConstantFacade $languageConstantFacade
     * @param \App\Model\LanguageConstant\LanguageConstantDataFactory $languageConstantDataFactory
     * @param \App\Model\LanguageConstant\LanguageConstantGridFactory $languageConstantGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly LanguageConstantFacade $languageConstantFacade,
        private readonly LanguageConstantDataFactory $languageConstantDataFactory,
        private readonly LanguageConstantGridFactory $languageConstantGridFactory,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @Route("/constant/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('Admin/Content/LanguageConstant/list.html.twig', [
            'gridView' => $grid?->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @Route("/constant/edit/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request): Response
    {
        $key = $request->query->get('key');
        $locale = $this->getSelectedLocale();
        $translation = $this->languageConstantFacade->getOriginalTranslationsByLocaleIndexedByKey($locale)[$key] ?? null;

        if ($translation === null) {
            $this->addErrorFlashTwig(
                t('Language constant translation <strong>{{ name }}</strong> not found'),
                [
                    'name' => $key,
                ],
            );

            return $this->redirectToRoute('admin_languageconstant_list');
        }

        $constant = $this->languageConstantFacade->findByKey($key);
        $constantData = $this->languageConstantDataFactory->createFromDataOrLanguageConstant($key, $locale, $translation, $constant);

        $form = $this->createForm(LanguageConstantFormType::class, $constantData)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $constant = $this->languageConstantFacade->createOrEdit($form->getData(), $constant);

                $this->addSuccessFlashTwig(
                    t('Language constant translation <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $constant->getKey(),
                        'url' => $this->generateUrl('admin_languageconstant_edit', ['key' => $constant->getKey()]),
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

            $this->languageConstantFacade->generateLanguageConstantFile($locale);

            return $this->redirectToRoute('admin_languageconstant_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/LanguageConstant/edit.html.twig', [
            'form' => $form->createView(),
            'constant' => $constant,
        ]);
    }

    /**
     * @Route("/constant/delete/")
     * @CsrfProtection
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request): Response
    {
        $key = $request->query->get('key');
        $constant = $this->languageConstantFacade->findByKey($key);

        if ($constant !== null) {
            try {
                $this->languageConstantFacade->delete($key, $this->getSelectedLocale());
                $this->languageConstantFacade->generateLanguageConstantFile($this->getSelectedLocale());

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

    /**
     * @return string
     */
    private function getSelectedLocale(): string
    {
        return $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();
    }
}
