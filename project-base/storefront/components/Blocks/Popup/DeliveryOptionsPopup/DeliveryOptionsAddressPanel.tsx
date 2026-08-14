import { TypeProductDeliveryOptionFragment } from 'graphql/requests/transports/fragments/ProductDeliveryOptionFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { DeliveryOptionRow } from './DeliveryOptionRow';

type DeliveryOptionsAddressPanelProps = {
    deliveryOptions: TypeProductDeliveryOptionFragment[];
};

export const DeliveryOptionsAddressPanel: FC<DeliveryOptionsAddressPanelProps> = ({ deliveryOptions }) => {
    const { t } = useTranslation();

    if (deliveryOptions.length === 0) {
        return <p className="text-sm text-text-less">{t('Delivery to address is not available for this product.')}</p>;
    }

    return (
        <div className="flex flex-col divide-y divide-border-less">
            {deliveryOptions.map((deliveryOption) => (
                <DeliveryOptionRow key={deliveryOption.transport.uuid} deliveryOption={deliveryOption} />
            ))}
        </div>
    );
};
