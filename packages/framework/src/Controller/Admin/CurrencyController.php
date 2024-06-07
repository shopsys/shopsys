<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencySettingsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid\CurrencyInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid\CurrencyInlineEdit $currencyInlineEdit
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrencyInlineEdit $currencyInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/currency/list/')]
    public function listAction()
    {
        $grid = $this->currencyInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Currency/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param int $id
     */
    #[Route(path: '/currency/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction($id)
    {
        try {
            $currency = $this->currencyFacade->getById($id);
            $message = t(
                'Do you really want to remove currency "%name%" permanently?',
                ['%name%' => $currency->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_currency_delete', $id);
        } catch (CurrencyNotFoundException $ex) {
            return new Response(t('Selected currency doesn\'t exist.'));
        }
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/currency/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction($id)
    {
        try {
            $fullName = $this->currencyFacade->getById($id)->getName();
            $this->currencyFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Currency <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (DeletingNotAllowedToDeleteCurrencyException $ex) {
            $this->addErrorFlash(
                t('This currency can\'t be deleted, it is set as default or is saved with order.'),
            );
        } catch (CurrencyNotFoundException $ex) {
            $this->addErrorFlash(t('Selected currency doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_currency_list');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingsAction(Request $request)
    {
        $domainNames = [];

        $currencySettingsFormData = [];
        $currencySettingsFormData['defaultCurrency'] = $this->currencyFacade->getDefaultCurrency();
        $currencySettingsFormData['domainDefaultCurrencies'] = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $currencySettingsFormData['domainDefaultCurrencies'][$domainId] =
                $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
            $domainNames[$domainId] = $domainConfig->getName();
        }

        $form = $this->createForm(CurrencySettingsFormType::class, $currencySettingsFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currencySettingsFormData = $form->getData();

            $this->currencyFacade->setDefaultCurrency($currencySettingsFormData['defaultCurrency']);

            foreach ($this->domain->getAll() as $domainConfig) {
                $domainId = $domainConfig->getId();
                $this->currencyFacade->setDomainDefaultCurrency(
                    $currencySettingsFormData['domainDefaultCurrencies'][$domainId],
                    $domainId,
                );
            }

            $this->addSuccessFlashTwig(t('Currency settings modified'));

            return $this->redirectToRoute('admin_currency_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Currency/currencySettings.html.twig', [
            'form' => $form->createView(),
            'domainNames' => $domainNames,
        ]);
    }
}
