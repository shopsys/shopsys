import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { notImplementedYetHandler } from 'components/Basic/NotImplementedYet/NotImplementedYet';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Button } from 'components/Forms/Button/Button';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { useRemoveCustomerUserMutation } from 'graphql/requests/customer/mutations/RemoveCustomerUserMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { CustomerUserRoleEnum } from 'types/customer';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useCurrentCustomerUsers } from 'utils/user/useCurrentCustomerUsers';

const DeleteCustomerUserPopup = dynamic(
    () =>
        import('components/Blocks/Popup/DeleteCustomerUserPopup').then(
            (component) => component.DeleteCustomerUserPopup,
        ),
    {
        ssr: false,
    },
);

const UsersPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const [, removeCustomerUser] = useRemoveCustomerUserMutation();
    const [customerUsersUrl, customerUserEditUrlByUuid] = getInternationalizedStaticUrls(
        ['/customer/users', { url: '/customer/users/:uuid', param: '' }],
        url,
    );
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Customer users'), slug: customerUsersUrl },
    ];
    const currentCustomerUsers = useCurrentCustomerUsers();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const deleteItemHandler = async (customerUserUuid: string | undefined) => {
        if (customerUserUuid === undefined) {
            return;
        }

        updatePortalContent(null);
        const deleteCustomerUserResult = await removeCustomerUser({ customerUserUuid });

        if (deleteCustomerUserResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting user'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('User has been deleted'));
    };

    const openDeleteCustomerUserPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        customerUserToBeDeletedUuid: string,
    ) => {
        e.stopPropagation();
        updatePortalContent(
            <DeleteCustomerUserPopup
                deleteCustomerUserHandler={() => deleteItemHandler(customerUserToBeDeletedUuid)}
            />,
        );
    };

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('Customer users')} title={t('Customer users')}>
                <div className="w-full flex flex-col gap-4">
                    <Button className="w-fit" size="small" variant="inverted" onClick={notImplementedYetHandler}>
                        {t('Add new user')}
                    </Button>
                    <Table className="w-full border-0 p-0">
                        {currentCustomerUsers.map((user) => (
                            <Row
                                key={user.uuid}
                                className="bg-tableBackground odd:bg-tableBackgroundContrast border-none"
                            >
                                <Cell className="py-2 text-left text-sm font-bold uppercase leading-5">
                                    {user.firstName} {user.lastName}
                                </Cell>

                                <Cell className="py-2 text-left text-sm leading-5">{user.email}</Cell>
                                <Cell align="right" className="py-2 text-sm leading-5">
                                    <ExtendedNextLink href={`${customerUserEditUrlByUuid}/${user.uuid}`}>
                                        <Button className="flex-1" size="small" variant="inverted">
                                            <EditIcon className="size-4" /> {t('Edit')}
                                        </Button>
                                    </ExtendedNextLink>
                                </Cell>
                                <Cell align="right" className="py-2 text-sm leading-5">
                                    <Button
                                        className="flex-1"
                                        size="small"
                                        variant="inverted"
                                        onClick={(e) => openDeleteCustomerUserPopup(e, user.uuid)}
                                    >
                                        <RemoveIcon className="size-4" /> {t('Delete')}
                                    </Button>
                                </Cell>
                            </Row>
                        ))}
                    </Table>
                </div>
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                authenticationRequired: true,
                authorizedRole: CustomerUserRoleEnum.ROLE_API_ALL,
                redisClient,
                domainConfig,
                t,
            }),
);

export default UsersPage;
