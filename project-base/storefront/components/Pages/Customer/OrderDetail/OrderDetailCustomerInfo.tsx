import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BillingAddressIcon } from 'components/Basic/Icon/BillingAddressIcon';
import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { isPacketeryTransport } from 'utils/packetery';

type OrderDetailCustomerInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailCustomerInfo: FC<OrderDetailCustomerInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const isPickupPlaceTransport =
        order.transport.isPersonalPickup || isPacketeryTransport(order.transport.transportTypeCode);

    return (
        <div className="flex w-full flex-col gap-6 vl:flex-row vl:flex-wrap xl:flex-nowrap">
            <InformationCard heading={t('Contact information')} icon={<UserIcon className="[&>path]:stroke-1" />}>
                <span>
                    {order.firstName} {order.lastName}
                </span>
                <ExtendedNextLink
                    className="hover:text-greyDark text-textAccent underline hover:no-underline"
                    href={`mailto:${order.email}`}
                >
                    {order.email}
                </ExtendedNextLink>
                <span>{order.telephone}</span>
            </InformationCard>

            <InformationCard
                heading={isPickupPlaceTransport ? t('Pickup place') : t('Delivery address')}
                icon={<MailIcon />}
            >
                <span>
                    {order.deliveryCompanyName && `${order.deliveryCompanyName}, `} {order.deliveryFirstName}{' '}
                    {order.deliveryLastName}
                </span>
                <span>{order.deliveryTelephone}</span>

                <span>
                    {order.deliveryStreet}, {order.deliveryCity}, {order.deliveryPostcode}
                </span>

                <span>{order.deliveryCountry?.name}</span>
            </InformationCard>

            <InformationCard heading={t('Billing address')} icon={<BillingAddressIcon />}>
                <span>{order.companyName}</span>

                <span>
                    {order.street}, {order.city}, {order.postcode}
                </span>

                <span>{order.companyNumber && `${t('Company number')}: ${order.companyNumber}`}</span>
                <span>{order.companyTaxNumber && `${t('Tax number')}: ${order.companyTaxNumber}`}</span>

                <span>{order.deliveryCountry?.name}</span>
            </InformationCard>
        </div>
    );
};
