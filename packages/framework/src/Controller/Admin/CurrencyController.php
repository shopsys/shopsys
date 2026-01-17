<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencySettingsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid\CurrencyInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class CurrencyController extends AdminBaseController
{
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrencyInlineEdit $currencyInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/currency/list/')]
    public function listAction(): Response
    {
        $grid = $this->currencyInlineEdit->getGrid();

        return $this->render('@ShopsysAdministration/content/currency/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/currency/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
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

    #[Route(path: '/currency/delete/{id}', requirements: ['id' => '\d+'])]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
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

    public function settingsAction(Request $request): Response
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

        return $this->render('@ShopsysAdministration/content/currency/currencySettings.html.twig', [
            'form' => $form->createView(),
            'domainNames' => $domainNames,
        ]);
    }
}
