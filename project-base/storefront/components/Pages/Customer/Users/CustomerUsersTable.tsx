import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
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
import { twJoin } from 'tailwind-merge';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
            <ManageCustomerUserPopup
                customerUser={customerUser}
                mode={customerUser ? 'edit' : 'add'}
                onSuccess={refetchCustomerUsers}
            />,
        );
    };

    const addNewUserButton = (
        <Button
            aria-haspopup="dialog"
            className="w-fit"
            data-tid={TIDs.customer_users_add_button}
            size="small"
            onClick={(e) => openManageCustomerUserPopup(e)}
        >
            {t('Add new user')}
        </Button>
    );

    if (customerUsersIsFetching) {
        return (
            <div className="flex w-full flex-col items-center gap-4">
                {addNewUserButton}
                <div className="flex w-full flex-col">
                    <SkeletonCustomerUsersTable />
                </div>
            </div>
        );
    }

    return (
        <div className="flex w-full flex-col items-center gap-4" data-tid={TIDs.customer_users_table}>
            {addNewUserButton}
            <Table className="w-full border-0 p-0">
                {customerUsers.map((user) => (
                    <Row
                        key={user.uuid}
                        className="mb-2 flex vl:table-row flex-col rounded-md border-none bg-table-bg-contrast vl:bg-table-bg-default vl:odd:bg-table-bg-contrast"
                    >
                        <Cell className="py-2 text-left font-bold text-sm uppercase leading-5">
                            {user.lastName} {user.firstName} {currentCustomerUserUuid === user.uuid && `(${t('You')})`}
                        </Cell>

                        <Cell
                            className={twJoin(
                                'vl:table-cell py-2 text-left text-sm leading-5',
                                'max-w-64 vl:max-w-56 overflow-x-auto whitespace-nowrap sm:max-w-full',
                                '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                            )}
                        >
                            {user.email}
                        </Cell>
                        <Cell className="vl:table-cell py-2 text-left text-sm leading-5">{user.roleGroup.name}</Cell>
                        <Cell align="right" className="flex vl:flex-row flex-row-reverse vl:justify-end gap-2 py-2">
                            <Button
                                className="flex-1"
                                data-tid={TIDs.customer_users_edit_button}
                                size="small"
                                variant="inverted"
                                onClick={(e) => openManageCustomerUserPopup(e, user)}
                            >
                                <EditIcon className="size-4" /> <span className="sm:block">{t('Edit')}</span>
                            </Button>
                            <Button
                                aria-haspopup="dialog"
                                className="flex-1"
                                hasDisabledLook={currentCustomerUserUuid === user.uuid}
                                disabled={currentCustomerUserUuid === user.uuid}
                                title={
                                    user.uuid === currentCustomerUserUuid
                                        ? t('You cannot delete your own account')
                                        : undefined
                                }
                                data-tid={TIDs.customer_users_delete_button}
                                size="small"
                                variant="inverted"
                                onClick={(e) => openDeleteCustomerUserPopup(e, user.uuid)}
                            >
                                <RemoveIcon className="size-4" /> <span className="sm:block">{t('Delete')}</span>
                            </Button>
                        </Cell>
                    </Row>
                ))}
            </Table>
        </div>
    );
};
