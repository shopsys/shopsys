import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BoxPackageHandIcon } from 'components/Basic/Icon/BoxPackageHandIcon';
import { UserProfileCardsIcon } from 'components/Basic/Icon/UserProfileCardsIcon';
import { WarehouseBoxPackageIcon } from 'components/Basic/Icon/WarehouseBoxPackageIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { isPacketeryTransport } from 'utils/packetery';

type OrderCustomerInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderCustomerInfo: FC<OrderCustomerInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const isPickupPlaceTransport =
        order.transport.isPersonalPickup || isPacketeryTransport(order.transport.transportTypeCode);

    return (
        <div className="bg-background-more vl:grid-cols-3 grid grid-cols-1 gap-2.5 rounded-xl p-5 lg:grid-cols-2">
            <InformationCard heading={t('Contact information')} icon={<UserProfileCardsIcon className="size-8" />}>
                <span>
                    {order.firstName} {order.lastName}
                </span>
                <ExtendedNextLink
                    href={`mailto:${order.email}`}
                    aria-label={t('Send email to {{ email }}', {
                        email: order.email,
                    })}
                    className={twJoin(
                        'text-text-default overflow-x-auto text-sm whitespace-nowrap underline hover:no-underline',
                        '[&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar]:h-1 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent',
                    )}
                >
                    {order.email}
                </ExtendedNextLink>
                <span>{order.telephone}</span>
            </InformationCard>

            <InformationCard
                heading={isPickupPlaceTransport ? t('Pickup place') : t('Delivery address')}
                icon={<BoxPackageHandIcon className="size-8" />}
            >
                <span>
                    {order.deliveryCompanyName && `${order.deliveryCompanyName}, `} {order.deliveryFirstName}{' '}
                    {order.deliveryLastName}
                </span>
                <span>{order.deliveryTelephone}</span>

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
