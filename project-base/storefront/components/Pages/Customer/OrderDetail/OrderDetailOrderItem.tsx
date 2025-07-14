import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { FillIcon } from 'components/Basic/Icon/FillIcon';
import { Image } from 'components/Basic/Image/Image';
import { CreateComplaintPopup } from 'components/Blocks/Popup/CreateComplaintPopup';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type OrderDetailOrderItemProps = {
    orderItem: TypeOrderDetailItemFragment;
    orderUuid: string;
    isDiscount?: boolean;
};

export const OrderDetailOrderItem: FC<OrderDetailOrderItemProps> = ({ orderItem, orderUuid, isDiscount }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { canCreateComplaint } = useAuthorization();

    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const openCreateComplaintPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        orderUuid?: string,
        orderItem?: TypeOrderDetailItemFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(<CreateComplaintPopup orderItem={orderItem} orderUuid={orderUuid} />);
    };

    if (isDiscount) {
        if (!isPriceVisible(orderItem.totalPrice.priceWithVat)) {
            return null;
        }

        return (
            <div className="flex items-center justify-between gap-2 pb-5">
                <span className="text-sm font-semibold">{orderItem.name}</span>

                {isPriceVisible(orderItem.totalPrice.priceWithVat) && (
                    <div className="font-secondary text-price-discounted font-bold whitespace-nowrap">
                        {formatPrice(mapPriceForCalculations(orderItem.totalPrice.priceWithVat))}
                    </div>
                )}
            </div>
        );
    }

    return (
        <div
            className={twJoin(
                'vl:gap-5 font-secondary flex items-center gap-3 font-semibold first:border-none first:pt-0 last:pb-0',
                'border-t-border-default border-t py-5',
            )}
        >
            <div
                className={twMergeCustom(
                    'vl:grid vl:grid-cols-[3fr_2fr_1fr_2fr] vl:gap-5 flex w-full flex-wrap items-center justify-between gap-3 border-b last:border-none',
                )}
            >
                <div className="vl:w-auto flex w-full items-center gap-2.5">
                    <div className="flex size-20 shrink-0">
                        <Image
                            alt={orderItem.name}
                            className="size-20 object-contain mix-blend-multiply"
                            height={80}
                            src={orderItem.product?.mainImage?.url}
                            width={80}
                        />
                    </div>

                    {orderItem.product?.isVisible ? (
                        <div className="flex flex-col gap-2">
                            <ExtendedNextLink
                                className="vl:w-fit text-text-default hover:text-text-hovered w-full text-sm no-underline hover:underline"
                                href={orderItem.product.slug}
                                skeletonType="product"
                                aria-label={t('Go to product {{ productName }}', {
                                    productName: orderItem.name,
                                })}
                            >
                                {orderItem.name}
                            </ExtendedNextLink>

                            {canCreateComplaint &&
                                isUserLoggedIn &&
                                orderItem.type === TypeOrderItemTypeEnum.Product && (
                                    <button
                                        aria-haspopup="dialog"
                                        className="text-link-default hover:text-link-hovered cursor-pointer self-baseline rounded-sm text-sm whitespace-nowrap underline outline-none"
                                        data-tid={TIDs.order_detail_create_complaint_button}
                                        tabIndex={0}
                                        aria-label={t('Create complaint for product {{ productName }}', {
                                            productName: orderItem.name,
                                        })}
                                        onClick={(e) => openCreateComplaintPopup(e, orderUuid, orderItem)}
                                    >
                                        <FillIcon className="mr-2 size-6" />
                                        {t('Create complaint')}
                                    </button>
                                )}
                        </div>
                    ) : (
                        <span className="text-text-default text-sm">{orderItem.name}</span>
                    )}
                </div>

                <span className="text-text-less vl:w-auto w-full text-sm">
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
