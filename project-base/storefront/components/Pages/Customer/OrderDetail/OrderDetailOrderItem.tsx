import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { CreateComplaintPopup } from 'components/Blocks/Popup/CreateComplaintPopup';
import { Button } from 'components/Forms/Button/Button';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type OrderDetailOrderItemProps = {
    orderItem: TypeOrderDetailItemFragment;
    orderUuid: string;
    isDiscount?: boolean;
};

export const OrderDetailOrderItem: FC<OrderDetailOrderItemProps> = ({ orderItem, orderUuid, isDiscount }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isUserLoggedIn = useIsUserLoggedIn();
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
        <div
            className={twJoin(
                'flex items-center gap-3 first:border-none first:pt-0 last:pb-0 vl:gap-5',
                isDiscount ? 'pb-5' : 'border-t border-t-borderAccentLess py-5',
            )}
        >
            {isDiscount ? (
                <div className="min-w-[60px]" />
            ) : (
                <Image alt={orderItem.name} height={60} src={orderItem.product?.mainImage?.url} width={60} />
            )}
            <div className="flex w-full flex-wrap items-center justify-between gap-3 border-b border-b-borderLess last:border-none vl:grid vl:grid-cols-[4fr_1fr_2fr_1fr] vl:gap-5">
                {isDiscount ? (
                    <span>{orderItem.name}</span>
                ) : orderItem.product?.isVisible ? (
                    <ExtendedNextLink className="w-full vl:w-fit" href={orderItem.product.slug} skeletonType="product">
                        {orderItem.name}
                    </ExtendedNextLink>
                ) : (
                    orderItem.name
                )}
                {isDiscount ? (
                    <div />
                ) : (
                    <span className="text-right">
                        {orderItem.quantity}
                        {orderItem.unit}
                    </span>
                )}
                {isPriceVisible(orderItem.totalPrice.priceWithVat) && (
                    <span className="text-right font-bold">{formatPrice(orderItem.totalPrice.priceWithVat)}</span>
                )}

                {isUserLoggedIn && orderItem.type === TypeOrderItemTypeEnum.Product && (
                    <Button
                        className="whitespace-nowrap"
                        size="small"
                        variant="inverted"
                        onClick={(e) => openCreateComplaintPopup(e, orderUuid, orderItem)}
                    >
                        {t('Create complaint')}
                    </Button>
                )}
            </div>
        </div>
    );
};
