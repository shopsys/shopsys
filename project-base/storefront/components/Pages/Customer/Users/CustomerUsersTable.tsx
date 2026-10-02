import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { StatusBadge } from 'components/Basic/StatusBadge/StatusBadge';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { SkeletonCustomerUsersTable } from 'components/Blocks/Skeleton/SkeletonModuleCustomerUsers';
import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { useRemoveCustomerUserMutation } from 'graphql/requests/customer/mutations/RemoveCustomerUserMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useCurrentCustomerUsers } from 'utils/user/useCurrentCustomerUsers';
import { CustomerUserActions } from './CustomerUserActions';

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

export const CustomerUsersTable: FC = () => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const [, removeCustomerUser] = useRemoveCustomerUserMutation();
    const { customerUsers, customerUsersIsFetching, refetchCustomerUsers } = useCurrentCustomerUsers();
    const { currentCustomerUserUuid } = useAuthorization();
    const handleError = useErrorHandler({
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while deleting user'),
    });

    const deleteItemHandler = async (customerUserUuid: string | undefined) => {
        if (customerUserUuid === undefined) {
            return;
        }

        closePortalContent();
        const deleteCustomerUserResult = await removeCustomerUser({ customerUserUuid });

        if (deleteCustomerUserResult.error !== undefined) {
            handleError(deleteCustomerUserResult.error);
            return;
        }

        showSuccessMessage(t('User has been deleted'));
    };

    const openDeleteCustomerUserPopup = (customerUser: TypeSimpleCustomerUserFragment) => {
        updatePortalContent(
            <DeleteCustomerUserPopup
                customerUserName={`${customerUser.firstName} ${customerUser.lastName}`}
                customerUserEmail={customerUser.email}
                deleteCustomerUserHandler={() => deleteItemHandler(customerUser.uuid)}
            />,
        );
    };

    const openManageCustomerUserPopup = (customerUser?: TypeSimpleCustomerUserFragment) => {
        updatePortalContent(
            <ManageCustomerUserPopup
                customerUser={customerUser}
                mode={customerUser ? 'edit' : 'add'}
                onSuccess={refetchCustomerUsers}
            />,
        );
    };

    return (
        <div className="flex w-full flex-col gap-6" data-tid={TIDs.customer_users_table}>
            <Button
                aria-haspopup="dialog"
                className="w-fit self-center"
                tid={TIDs.customer_users_add_button}
                size="small"
                onClick={() => openManageCustomerUserPopup()}
            >
                <PlusIcon aria-hidden="true" className="size-4" />
                {t('Add new user')}
            </Button>

            {customerUsersIsFetching ? (
                <SkeletonCustomerUsersTable />
            ) : (
                <Table
                    className="w-full"
                    tableClassName="table-fixed"
                    head={
                        <Row className="hidden border-border-less/50 border-b bg-background-default odd:bg-background-default sm:table-row">
                            <Cell
                                isHead
                                scope="col"
                                className="w-1/2 py-3 text-left font-medium text-text-less text-xs"
                            >
                                {t('User')}
                            </Cell>
                            <Cell isHead scope="col" className="py-3 text-left font-medium text-text-less text-xs">
                                {t('Role group')}
                            </Cell>
                            <Cell
                                isHead
                                scope="col"
                                align="right"
                                className="w-28 py-3 font-medium text-text-less text-xs"
                            >
                                {t('Actions')}
                            </Cell>
                        </Row>
                    }
                >
                    {customerUsers.map((user) => (
                        <Row
                            key={user.uuid}
                            className="grid grid-cols-[minmax(0,1fr)_auto] items-center border-border-less/50 border-b bg-background-default py-3 transition-colors odd:bg-background-default hover:bg-background-more sm:table-row sm:py-0"
                        >
                            <Cell className="col-span-2 py-2 sm:py-4">
                                <div className="flex flex-wrap items-center gap-2 font-semibold">
                                    <span className="wrap-anywhere">
                                        {user.firstName} {user.lastName}
                                    </span>
                                    {currentCustomerUserUuid === user.uuid && (
                                        <StatusBadge variant="info">{t('You')}</StatusBadge>
                                    )}
                                </div>
                                <a
                                    className="wrap-anywhere mt-1 inline-block text-text-less text-xs no-underline hover:text-link-hovered hover:underline focus-visible:outline-2 focus-visible:outline-border-active"
                                    href={`mailto:${user.email}`}
                                >
                                    {user.email}
                                </a>
                            </Cell>
                            <Cell className="py-2 sm:py-4">
                                <StatusBadge className="max-w-full text-wrap" variant="neutral">
                                    {user.roleGroup.name}
                                </StatusBadge>
                            </Cell>
                            <Cell align="right" className="py-2 sm:py-4">
                                <CustomerUserActions
                                    customerUser={user}
                                    isCurrentUser={currentCustomerUserUuid === user.uuid}
                                    onDelete={() => openDeleteCustomerUserPopup(user)}
                                    onEdit={() => openManageCustomerUserPopup(user)}
                                />
                            </Cell>
                        </Row>
                    ))}
                </Table>
            )}
        </div>
    );
};
