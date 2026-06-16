<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriberNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

#[ForRole(AdminRoleConstant::ROLE_NEWSLETTER)]
class NewsletterController extends AdminBaseController
{
    public function __construct(
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/newsletter/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->newsletterFacade->getQueryBuilderForQuickSearch(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $quickSearchForm->getData(),
        );

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'u.id');
        $grid = $this->gridFactory->create('customerList', $dataSource, AdminRoleConstant::ROLE_NEWSLETTER);
        $grid->enablePaging();

        $grid->addColumn('email', 'email', 'Email');
        $grid->addColumn('createdAt', 'createdAt', t('Subscribed at'));
        $grid->setDefaultOrder('email');
        $grid->addDeleteActionColumn('admin_newsletter_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove this subscriber?'));

        $grid->setTheme('@ShopsysAdministration/content/newsletter/listGrid.html.twig');

        return $this->render(
            '@ShopsysAdministration/content/newsletter/list.html.twig',
            [
                'quickSearchForm' => $quickSearchForm->createView(),
                'gridView' => $grid->createView(),
            ],
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/newsletter/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $newsletterSubscriber = $this->newsletterFacade->getNewsletterSubscriberById($id);

            $this->newsletterFacade->delete($newsletterSubscriber);

            $this->addSuccessFlashTwig(
                t('Subscriber <strong>{{ email }}</strong> deleted'),
                [
                    'email' => $newsletterSubscriber->getEmail(),
                ],
            );
        } catch (NewsletterSubscriberNotFoundException) {
            $this->addErrorFlash(t('Selected subscriber doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_newsletter_list');
    }

    #[Route(path: '/newsletter/export-csv/')]
    #[CanView]
    public function exportAction(): Response
    {
        return new CsvResponse(
            $this->getCsvExportData($this->adminDomainTabsFacade->getSelectedDomainId()),
            'emails.csv',
            null,
            [
                CsvEncoder::DELIMITER_KEY => ';',
                CsvEncoder::NO_HEADERS_KEY => true,
            ],
        );
    }

    /**
     * @return iterable<int, array{0: string, 1: string}>
     */
    protected function getCsvExportData(int $domainId): iterable
    {
        $emailsDataIterator = $this->newsletterFacade->getAllEmailsDataIteratorByDomainId($domainId);

        foreach ($emailsDataIterator as $emailData) {
            yield [
                $emailData['email'],
                $this->formatCreatedAt($emailData['createdAt']),
            ];
        }
    }

    protected function formatCreatedAt(mixed $createdAt): string
    {
        if ($createdAt instanceof DateTimeInterface) {
            return $createdAt->format(DATE_ATOM);
        }

        return (string)$createdAt;
    }
}
