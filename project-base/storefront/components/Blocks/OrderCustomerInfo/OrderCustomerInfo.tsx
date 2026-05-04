import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BoxPackageHandIcon } from 'components/Basic/Icon/BoxPackageHandIcon';
import { UserProfileCardsIcon } from 'components/Basic/Icon/UserProfileCardsIcon';
import { WarehouseBoxPackageIcon } from 'components/Basic/Icon/WarehouseBoxPackageIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { twJoin } from 'tailwind-merge';
import { normalizeTelephone } from 'utils/formaters/normalizeTelephone';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getOrderTransportItem } from 'utils/mappers/order';
import { isPacketeryTransport } from 'utils/packetery';

type OrderCustomerInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderCustomerInfo: FC<OrderCustomerInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const orderTransport = getOrderTransportItem(order.items);
    const isPickupPlaceTransport =
        orderTransport &&
        (orderTransport.transport?.isPersonalPickup ||
            isPacketeryTransport(orderTransport.transport?.transportTypeCode));

    return (
        <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-xl bg-background-more p-5 lg:grid-cols-2">
            <InformationCard heading={t('Contact information')} icon={<UserProfileCardsIcon className="size-8" />}>
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
                heading={isPickupPlaceTransport ? t('Pickup place') : t('Delivery address')}
                icon={<BoxPackageHandIcon className="size-8" />}
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

            <InformationCard heading={t('Billing address')} icon={<WarehouseBoxPackageIcon className="size-8" />}>
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
