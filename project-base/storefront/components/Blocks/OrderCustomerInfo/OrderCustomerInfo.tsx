import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BillingAddressIcon } from 'components/Basic/Icon/BillingAddressIcon';
import { ContactInformationsIcon } from 'components/Basic/Icon/ContactInformationsIcon';
import { DeliveryAddressIcon } from 'components/Basic/Icon/DeliveryAddressIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { twJoin } from 'tailwind-merge';
import { normalizeTelephone } from 'utils/formaters/normalizeTelephone';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getOrderTransportItem } from 'utils/mappers/order';
import { isPickupPlaceTransport } from 'utils/transport';

type OrderCustomerInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderCustomerInfo: FC<OrderCustomerInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const orderTransport = getOrderTransportItem(order.items);
    const isPickupPlaceOrder = orderTransport && isPickupPlaceTransport(orderTransport.transport?.transportTypeCode);

    return (
        <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-xl bg-background-more p-5 lg:grid-cols-2">
            <InformationCard heading={t('Contact information')} icon={<ContactInformationsIcon className="size-8" />}>
                <span>
                    {order.firstName} {order.lastName}
                </span>
                <ExtendedNextLink
                    aria-label={t('Send email to {{ email }}', { ns: 'accessibility', email: order.email })}
                    href={`mailto:${order.email}`}
                    className={twJoin(
                        'overflow-x-auto whitespace-nowrap text-sm text-text-default underline hover:no-underline',
                        '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                    )}
                >
                    {order.email}
                </ExtendedNextLink>
                <span>{normalizeTelephone(order.telephone)}</span>
            </InformationCard>

            <InformationCard
                heading={isPickupPlaceOrder ? t('Pickup place') : t('Delivery address')}
                icon={<DeliveryAddressIcon className="size-8" />}
            >
                <span>
                    {order.deliveryCompanyName && `${order.deliveryCompanyName}, `} {order.deliveryFirstName}{' '}
                    {order.deliveryLastName}
                </span>
                <span>{normalizeTelephone(order.deliveryTelephone)}</span>

                <span>{order.deliveryStreet}</span>

                <span>
                    {order.deliveryCity}, {order.deliveryPostcode}
                </span>

                <span>{order.deliveryCountry?.name}</span>
            </InformationCard>

            <InformationCard heading={t('Billing address')} icon={<BillingAddressIcon className="size-8" />}>
                <span>{order.companyName}</span>

                <span>{order.street}</span>

                <span>
                    {order.city}, {order.postcode}
                </span>

                <span>{order.country.name}</span>

                <span>{order.companyNumber && `${t('Company number')}: ${order.companyNumber}`}</span>

                <span>{order.companyTaxNumber && `${t('Tax number')}: ${order.companyTaxNumber}`}</span>
            </InformationCard>
        </div>
    );
};
