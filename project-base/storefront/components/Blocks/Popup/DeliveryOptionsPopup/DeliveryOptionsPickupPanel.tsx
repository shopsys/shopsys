import { TypeProductDeliveryOptionFragment } from 'graphql/requests/transports/fragments/ProductDeliveryOptionFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { DeliveryOptionsPickupTransport } from './DeliveryOptionsPickupTransport';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';

type DeliveryOptionsPickupPanelProps = {
    pickupDeliveryOptions: TypeProductDeliveryOptionFragment[];
    product: DeliveryOptionsProduct;
    scrollableTargetId: string;
};

export const DeliveryOptionsPickupPanel: FC<DeliveryOptionsPickupPanelProps> = ({
    pickupDeliveryOptions,
    product,
    scrollableTargetId,
}) => {
    const { t } = useTranslation();

    if (pickupDeliveryOptions.length === 0) {
        return <p className="text-sm text-text-less">{t('Pickup at a store is not available for this product.')}</p>;
    }

    return (
        <div className="flex flex-col gap-5">
            {pickupDeliveryOptions.map((pickupDeliveryOption) => (
                <DeliveryOptionsPickupTransport
                    key={pickupDeliveryOption.transport.uuid}
                    deliveryOption={pickupDeliveryOption}
                    product={product}
                    scrollableTargetId={scrollableTargetId}
                />
            ))}
        </div>
    );
};
