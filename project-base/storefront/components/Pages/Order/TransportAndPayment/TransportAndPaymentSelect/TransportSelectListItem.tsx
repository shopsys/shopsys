import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { useTransportChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportStoresFragment } from 'graphql/requests/transports/fragments/TransportStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { memo } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type ChangeTransport = ReturnType<typeof useTransportChangeInSelect>['changeTransport'];

type TransportListItemProps = {
    transport:
        | (TypeTransportWithAvailablePaymentsFragment & TypeTransportStoresFragment)
        | TypeTransportWithAvailablePaymentsFragment;
    isActive?: boolean;
    changeTransport: ChangeTransport;
    pickupPlace: StoreOrPacketeryPoint | null;
    openPickupPlacePopup?: () => void;
};

const TransportListItemComp: FC<TransportListItemProps> = ({
    transport,
    isActive = false,
    changeTransport,
    pickupPlace,
    openPickupPlacePopup,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <TransportAndPaymentListItem key={transport.uuid}>
            <Radiobutton
                checked={isActive}
                id={transport.uuid}
                name="transport"
                value={transport.uuid}
                aria-label={t('Choose transport {{ transportName }} for {{ price }}', {
                    transportName: transport.name,
                    price: formatPrice(transport.price.priceWithVat),
                })}
                label={
                    <TransportAndPaymentSelectItemLabel
                        daysUntilDelivery={transport.daysUntilDelivery}
                        description={transport.description}
                        image={transport.mainImage}
                        name={transport.name}
                        openPickupPlacePopup={() => openPickupPlacePopup?.()}
                        pickupPlaceDetail={isActive && pickupPlace ? pickupPlace : undefined}
                        price={transport.price}
                        showChangeButton={isActive}
                    />
                }
                onClick={changeTransport}
            />
        </TransportAndPaymentListItem>
    );
};

export const TransportListItem = memo(TransportListItemComp);
