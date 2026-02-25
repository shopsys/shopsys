import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { useTransportChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportStoresFragment } from 'graphql/requests/transports/fragments/TransportStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type ChangeTransport = ReturnType<typeof useTransportChangeInSelect>['changeTransport'];

type TransportListItemProps = {
    transport:
        | (TypeTransportWithAvailablePaymentsFragment & TypeTransportStoresFragment)
        | TypeTransportWithAvailablePaymentsFragment;
    isActive?: boolean;
    disabled?: boolean;
    changeTransport: ChangeTransport;
    pickupPlace: StoreOrPacketeryPoint | null;
    openPickupPlacePopup?: () => void;
};

export const TransportListItem: FC<TransportListItemProps> = ({
    transport,
    isActive = false,
    disabled,
    changeTransport,
    pickupPlace,
    openPickupPlacePopup,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    const ariaLabel = isPriceVisible(transport.price.priceWithVat)
        ? t('Choose transport {{ transportName }} for {{ price }}', {
              ns: 'accessibility',
              transportName: transport.name,
              price: formatPrice(transport.price.priceWithVat),
          })
        : t('Choose transport {{ transportName }}', {
              ns: 'accessibility',
              transportName: transport.name,
          });

    return (
        <TransportAndPaymentListItem key={transport.uuid}>
            <Radiobutton
                aria-label={ariaLabel}
                checked={isActive}
                disabled={disabled}
                id={transport.uuid}
                name="transport"
                value={transport.uuid}
                label={
                    <TransportAndPaymentSelectItemLabel
                        daysUntilDelivery={transport.daysUntilDelivery}
                        description={transport.description}
                        disabled={disabled}
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
