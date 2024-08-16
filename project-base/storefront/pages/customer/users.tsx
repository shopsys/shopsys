import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Button } from 'components/Forms/Button/Button';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { useRemoveCustomerUserMutation } from 'graphql/requests/customer/mutations/RemoveCustomerUserMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { CustomerUserAreaEnum, CustomerUserRoleEnum } from 'types/customer';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useCurrentCustomerUsers } from 'utils/user/useCurrentCustomerUsers';
import { useRedirectOnPermissionsChange } from 'utils/user/useRedirectOnPermissionsChange';

const DeleteCustomerUserPopup = dynamic(
    () =>
        import('components/Blocks/Popup/DeleteCustomerUserPopup').then(
            (component) => component.DeleteCustomerUserPopup,
        ),
    {
        ssr: false,
    },
);

const ManageCustomerUserPopup = dynamic(
    () =>
        import('components/Blocks/Popup/ManageCustomerUserPopup').then(
            (component) => component.ManageCustomerUserPopup,
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
    const [customerUsersUrl] = getInternationalizedStaticUrls(['/customer/users'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Customer users'), slug: customerUsersUrl },
    ];
    const currentCustomerUsers = useCurrentCustomerUsers();
    const { uuid, canManageUsers } = useCurrentCustomerUserPermissions();
    const { redirect } = useRedirectOnPermissionsChange();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    if (canManageUsers === false) {
        redirect();
    }

    const deleteItemHandler = async (customerUserUuid: string | undefined) => {
        if (customerUserUuid === undefined) {
            return;
        }

        updatePortalContent(null);
        const deleteCustomerUserResult = await removeCustomerUser({ customerUserUuid });

        if (deleteCustomerUserResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(deleteCustomerUserResult.error, t);

            showErrorMessage(
                applicationError?.message ? applicationError.message : t('There was an error while deleting user'),
                GtmMessageOriginType.other,
            );
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

    const openManageCustomerUserPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        customerUser?: TypeSimpleCustomerUserFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(
            <ManageCustomerUserPopup customerUser={customerUser} mode={customerUser ? 'edit' : 'add'} />,
        );
    };

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('Customer users')} title={t('Customer users')}>
                <div className="w-full flex flex-col gap-4">
                    <Button
                        className="w-fit"
                        size="small"
                        variant="inverted"
                        onClick={(e) => openManageCustomerUserPopup(e)}
                    >
                        {t('Add new user')}
                    </Button>
                    <Table className="w-full border-0 p-0">
                        {currentCustomerUsers.map((user) => (
                            <Row
                                key={user.uuid}
                                className="bg-tableBackground odd:bg-tableBackgroundContrast border-none"
                            >
                                <Cell className="py-2 text-left text-sm font-bold uppercase leading-5">
                                    {user.firstName} {user.lastName} {uuid === user.uuid && '(You)'}
                                </Cell>

                                <Cell className="py-2 text-left text-sm leading-5 hidden vl:table-cell">
                                    {user.email}
                                </Cell>
                                <Cell align="right" className="flex justify-end gap-2 py-2 text-sm leading-5">
                                    <Button
                                        size="small"
                                        variant="inverted"
                                        onClick={(e) => openManageCustomerUserPopup(e, user)}
                                    >
                                        <EditIcon className="size-4" />{' '}
                                        <span className="hidden sm:block">{t('Edit')}</span>
                                    </Button>
                                    <Button
                                        size="small"
                                        variant="inverted"
                                        onClick={(e) => openDeleteCustomerUserPopup(e, user.uuid)}
                                    >
                                        <RemoveIcon className="size-4" />{' '}
                                        <span className="hidden sm:block">{t('Delete')}</span>
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
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [CustomerUserRoleEnum.ROLE_API_ALL],
                    authorizedAreas: [CustomerUserAreaEnum.B2B],
                },
                redisClient,
                domainConfig,
                t,
            }),
);

export default UsersPage;
