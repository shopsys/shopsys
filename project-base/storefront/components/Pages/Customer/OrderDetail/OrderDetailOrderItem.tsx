import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { FillIcon } from 'components/Basic/Icon/FillIcon';
import { Image } from 'components/Basic/Image/Image';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type OrderDetailOrderItemProps = {
    orderItem: TypeOrderDetailItemFragment;
    orderUuid: string;
    isDiscount?: boolean;
    isOrderFromRegisteredCustomer: boolean;
};

export const OrderDetailOrderItem: FC<OrderDetailOrderItemProps> = ({
    orderItem,
    orderUuid,
    isDiscount,
    isOrderFromRegisteredCustomer,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { canCreateComplaint } = useAuthorization();
    const isProductGift = orderItem.type === TypeOrderItemTypeEnum.ProductGift;
    const showComplaintButton =
        canCreateComplaint &&
        isUserLoggedIn &&
        isOrderFromRegisteredCustomer &&
        orderItem.order.withdrawalRequest === null &&
        orderItem.type === TypeOrderItemTypeEnum.Product;

    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const openCreateComplaintPopup = async (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        orderUuid?: string,
        orderItem?: TypeOrderDetailItemFragment,
    ) => {
        e.stopPropagation();
        const { CreateComplaintPopup } = await import('components/Blocks/Popup/CreateComplaintPopup');
        updatePortalContent(<CreateComplaintPopup orderItem={orderItem} orderUuid={orderUuid} />);
    };

    if (isDiscount) {
        if (!isPriceVisible(orderItem.totalPrice.priceWithVat)) {
            return null;
        }

        return (
            <div className="flex items-center justify-between gap-2 pb-5">
                <span className="font-semibold text-sm">{orderItem.name}</span>

                {isPriceVisible(orderItem.totalPrice.priceWithVat) && (
                    <div className="whitespace-nowrap font-bold font-secondary text-price-discounted">
                        {formatPrice(mapPriceForCalculations(orderItem.totalPrice.priceWithVat))}
                    </div>
                )}
            </div>
        );
    }

    return (
        <div
            className={twJoin(
                'relative flex items-center gap-3 vl:gap-5 font-secondary font-semibold first:border-none',
                'border-t border-t-border-default py-5 first:pt-0 last:pb-0',
            )}
        >
            {isProductGift && <GiftBadge className="top-5 rounded-tl-md" />}

            <div
                className={twMergeCustom(
                    'flex vl:grid w-full vl:grid-cols-[3fr_2fr_1fr_2fr] flex-wrap items-center justify-between gap-3 vl:gap-5 border-b last:border-none',
                )}
            >
                <div className="flex vl:w-auto w-full items-center gap-2.5">
                    <div className="flex size-20 shrink-0" data-tid={TIDs.order_detail_item_image}>
                        <Image
                            alt={orderItem.name}
                            className="size-20 object-contain mix-blend-multiply"
                            height={80}
                            src={orderItem.product?.mainImage?.url}
                            width={80}
                        />
                    </div>

                    <div className="flex flex-col gap-2">
                        {orderItem.product?.isVisible ? (
                            <ExtendedNextLink
                                className="vl:w-fit w-full text-sm text-text-default no-underline hover:text-text-hovered hover:underline"
                                href={orderItem.product.slug}
                                skeletonType="product"
                                aria-label={t('Go to product {{ productName }}', {
                                    ns: 'accessibility',
                                    productName: orderItem.name,
                                })}
                            >
                                {orderItem.name}
                            </ExtendedNextLink>
                        ) : (
                            <span className="text-sm text-text-default">{orderItem.name}</span>
                        )}

                        {showComplaintButton && (
                            <button
                                aria-haspopup="dialog"
                                className="cursor-pointer self-baseline whitespace-nowrap rounded-sm text-link-default text-sm underline outline-hidden hover:text-link-hovered"
                                data-tid={TIDs.order_detail_create_complaint_button}
                                tabIndex={0}
                                aria-label={t('Create complaint for product {{ productName }}', {
                                    ns: 'accessibility',
                                    productName: orderItem.name,
                                })}
                                onClick={(e) => openCreateComplaintPopup(e, orderUuid, orderItem)}
                            >
                                <FillIcon className="mr-2 size-6" />
                                {t('Create complaint')}
                            </button>
                        )}
                    </div>
                </div>

                <span className="vl:w-auto w-full text-sm text-text-less">
                    {t('Code')}: {orderItem.product?.catalogNumber}
                </span>

                <span>
                    {orderItem.quantity} {orderItem.unit}
                </span>

                {isPriceVisible(orderItem.totalPrice.priceWithVat) && (
                    <span className="text-right font-bold">{formatPrice(orderItem.totalPrice.priceWithVat)}</span>
                )}
            </div>
        </div>
    );
};
