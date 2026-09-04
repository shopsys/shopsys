import { TransportAndPaymentItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentItemLabel';
import { TIDs } from 'cypress/tids';
import { TypeProductDeliveryOptionFragment } from 'graphql/requests/transports/fragments/ProductDeliveryOptionFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPersonalPickupTransport } from 'utils/transport';

type DeliveryOptionRowProps = {
    deliveryOption: TypeProductDeliveryOptionFragment;
};

export const DeliveryOptionRow: FC<DeliveryOptionRowProps> = ({ deliveryOption }) => {
    const { t } = useTranslation();

    return (
        <div className="py-2.5" data-tid={TIDs.delivery_options_transport_row_ + deliveryOption.transport.uuid}>
            <TransportAndPaymentItemLabel
                description={deliveryOption.transport.description}
                expectedDeliveryDate={deliveryOption.expectedDeliveryDate ?? null}
                image={deliveryOption.transport.mainImage}
                isPersonalPickup={isPersonalPickupTransport(deliveryOption.transport.transportTypeCode)}
                isPriceNextToDeliveryDateOnSmallScreen
                name={deliveryOption.transport.name}
                price={deliveryOption.price}
                unknownDeliveryDateExplanation={t(
                    'The product is out of stock and we do not know its restocking date yet',
                )}
            />
        </div>
    );
};
