import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { getTransportUnavailabilityHeading } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportStoresFragment } from 'graphql/requests/transports/fragments/TransportStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { KeyboardEvent, MouseEvent } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { isPickupPlaceTransport } from 'utils/transport';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { TransportUnavailabilityInfo } from './TransportUnavailabilityInfo';

type ChangeTransport = (
    updatedTransportUuid: string | null,
    event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>,
) => void;

type TransportListItemProps = {
    transport:
        | (TypeTransportWithAvailablePaymentsFragment & TypeTransportStoresFragment)
        | TypeTransportWithAvailablePaymentsFragment;
    isActive?: boolean;
    hasGreyBackground?: boolean;
    disabled?: boolean;
    changeTransport: ChangeTransport;
    pickupPlace: StoreOrPacketeryPoint | null;
    openPickupPlacePopup?: () => void;
};

export const TransportListItem: FC<TransportListItemProps> = ({
    transport,
    isActive = false,
    hasGreyBackground = false,
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
        <TransportAndPaymentListItem
            key={transport.uuid}
            className={twJoin(
                'group mb-3 rounded-xl border border-transparent py-0 transition last:mb-0 last:border-b',
                (hasGreyBackground || isActive) && 'bg-background-more',
                !isActive && !disabled && 'hover:border-border-less hover:bg-background-default',
            )}
        >
            <Radiobutton
                aria-label={ariaLabel}
                checked={isActive}
                disabled={disabled}
                id={transport.uuid}
                name="transport"
                shouldUseFocusOnlyArrowKeys
                value={transport.uuid}
                label={
                    <TransportAndPaymentSelectItemLabel
                        description={transport.description}
                        expectedDeliveryDate={transport.expectedDeliveryDate}
                        disabled={disabled}
                        image={transport.mainImage}
                        isActive={isActive}
                        isImageOnWhiteBackground={hasGreyBackground || isActive}
                        isPersonalPickup={isPickupPlaceTransport(transport.transportTypeCode)}
                        name={transport.name}
                        openPickupPlacePopup={() => openPickupPlacePopup?.()}
                        pickupPlaceDetail={isActive && pickupPlace ? pickupPlace : undefined}
                        price={transport.price}
                        showChangeButton={isActive}
                    />
                }
                labelWrapperClassName={twJoin(
                    'rounded-xl px-4 vl:px-5 py-4 transition peer-focus-visible:bg-orange-500 peer-focus-visible:outline-hidden [&>span:first-child]:hidden',
                )}
                onClick={changeTransport}
            />

            {disabled &&
                transport.productsBlockingSelectionInCart.map((productsGroup) => (
                    <TransportUnavailabilityInfo
                        key={productsGroup.reason}
                        heading={getTransportUnavailabilityHeading(productsGroup.reason, t)}
                        products={productsGroup.products}
                    />
                ))}
        </TransportAndPaymentListItem>
    );
};
