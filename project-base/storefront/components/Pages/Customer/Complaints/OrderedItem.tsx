import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

const CreateComplaintPopup = dynamic(
    () => import('components/Blocks/Popup/CreateComplaintPopup').then((component) => component.CreateComplaintPopup),
    {
        ssr: false,
    },
);

type OrderedItemProps = {
    orderedItem: TypeOrderDetailItemFragment;
};

export const OrderedItem: FC<OrderedItemProps> = ({ orderedItem }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const { url } = useDomainConfig();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openCreateComplaintPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        orderUuid: string,
        orderItem: TypeOrderDetailItemFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(<CreateComplaintPopup orderItem={orderItem} orderUuid={orderUuid} />);
    };

    return (
        <div className="bg-backgroundMore flex flex-col gap-5 rounded-md p-4 vl:p-6">
            <div className="flex flex-col vl:flex-row vl:justify-between vl:items-start gap-6">
                <div className="flex flex-col gap-1">
                    <h5>{orderedItem.name}</h5>
                    <div className="flex gap-x-8 gap-y-2 flex-wrap">
                        <OrderedItemColumnInfo
                            title={t('Order number')}
                            value={
                                <ExtendedNextLink
                                    type="order"
                                    href={{
                                        pathname: customerOrderDetailUrl,
                                        query: { orderNumber: orderedItem.order.number },
                                    }}
                                >
                                    {orderedItem.order.number}
                                </ExtendedNextLink>
                            }
                        />
                        <OrderedItemColumnInfo
                            title={t('Creation date')}
                            value={formatDateAndTime(orderedItem.order.creationDate)}
                        />
                        <OrderedItemColumnInfo
                            title={t('Quantity')}
                            value={`${orderedItem.quantity} ${orderedItem.unit}`}
                            wrapperClassName="w-20"
                        />
                        <OrderedItemColumnInfo
                            title={t('Price')}
                            value={formatPrice(orderedItem.totalPrice.priceWithVat)}
                            valueClassName="text-price"
                            wrapperClassName="min-w-[80px]"
                        />
                    </div>
                </div>
                <div className="flex gap-2 items-center">
                    <Button
                        size="small"
                        onClick={(e) => openCreateComplaintPopup(e, orderedItem.order.number, orderedItem)}
                    >
                        {t('Create complaint')}
                    </Button>
                </div>
            </div>
        </div>
    );
};

type OrderedItemColumnInfoProps = {
    title: string;
    value: ReactNode;
    valueClassName?: string;
    wrapperClassName?: string;
};

const OrderedItemColumnInfo: FC<OrderedItemColumnInfoProps> = ({ title, value, valueClassName, wrapperClassName }) => {
    return (
        <div className={twMergeCustom('flex gap-4 items-end', wrapperClassName)}>
            <div className="flex flex-col gap-1">
                <span className="text-sm">{title}</span>
                <span className={twMergeCustom('font-bold leading-none', valueClassName)}>{value}</span>
            </div>
        </div>
    );
};
