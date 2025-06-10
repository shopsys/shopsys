import { CreateComplaintPopup } from 'components/Blocks/Popup/CreateComplaintPopup';
import { Button, ButtonProps } from 'components/Forms/Button/Button';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { useSessionStore } from 'store/useSessionStore';

type CreateComplaintButtonProps = ButtonProps & {
    label: string;
    orderItem?: TypeOrderDetailItemFragment;
    orderUuid?: string;
};

export const CreateComplaintPopupButton: FC<CreateComplaintButtonProps> = ({
    label,
    orderItem,
    orderUuid,
    ...props
}) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const openCreateComplaintPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        orderUuid?: string,
        orderItem?: TypeOrderDetailItemFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(<CreateComplaintPopup orderItem={orderItem} orderUuid={orderUuid} />);
    };

    return (
        <Button aria-haspopup="dialog" onClick={(e) => openCreateComplaintPopup(e, orderUuid, orderItem)} {...props}>
            {label}
        </Button>
    );
};
