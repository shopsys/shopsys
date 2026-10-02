import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { UserEditIcon } from 'components/Basic/Icon/UserEditIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type CustomerUserActionsProps = {
    customerUser: TypeSimpleCustomerUserFragment;
    isCurrentUser: boolean;
    onDelete: () => void;
    onEdit: () => void;
};

export const CustomerUserActions: FC<CustomerUserActionsProps> = ({
    customerUser,
    isCurrentUser,
    onDelete,
    onEdit,
}) => {
    const { t } = useTranslation();
    const userName = `${customerUser.firstName} ${customerUser.lastName}`;

    return (
        <div className="ml-auto grid w-fit grid-cols-2 items-center gap-2">
            <IconButton
                Icon={UserEditIcon}
                aria-haspopup="dialog"
                ariaLabel={`${t('Edit')}: ${userName}`}
                shape="rounded"
                size="small"
                tid={TIDs.customer_users_edit_button}
                title={t('Edit')}
                tooltipLabel={t('Edit')}
                variant="ghost"
                onClick={onEdit}
            />
            {!isCurrentUser && (
                <IconButton
                    Icon={TrashCanIcon}
                    aria-haspopup="dialog"
                    ariaLabel={`${t('Delete')}: ${userName}`}
                    className="text-text-error hover:text-text-error"
                    shape="rounded"
                    size="small"
                    tid={TIDs.customer_users_delete_button}
                    title={t('Delete')}
                    tooltipLabel={t('Delete')}
                    variant="ghost"
                    onClick={onDelete}
                />
            )}
        </div>
    );
};
