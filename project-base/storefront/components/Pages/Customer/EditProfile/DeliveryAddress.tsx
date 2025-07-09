import { AddressList } from './AddressList';
import { Button } from 'components/Forms/Button/Button';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryAddressType } from 'types/customer';

const DeliveryAddressPopup = dynamic(
    () => import('components/Blocks/Popup/DeliveryAddressPopup').then((component) => component.DeliveryAddressPopup),
    {
        ssr: false,
    },
);

type DeliveryAddressProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

export const DeliveryAddress: FC<DeliveryAddressProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const { t } = useTranslation();
    const { canManagePersonalData } = useAuthorization();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openDeliveryAddressPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();
        updatePortalContent(<DeliveryAddressPopup />);
    };

    return (
        <FormBlockWrapper className="border-b-0">
            <FormHeading className="flex justify-between">
                {t('Delivery addresses')}
                {canManagePersonalData && (
                    <Button
                        aria-haspopup="dialog"
                        size="small"
                        variant="inverted"
                        onClick={(e) => openDeliveryAddressPopup(e)}
                    >
                        {t('Add new address')}
                    </Button>
                )}
            </FormHeading>

            <FormLine>
                <AddressList defaultDeliveryAddress={defaultDeliveryAddress} deliveryAddresses={deliveryAddresses} />
            </FormLine>
        </FormBlockWrapper>
    );
};
