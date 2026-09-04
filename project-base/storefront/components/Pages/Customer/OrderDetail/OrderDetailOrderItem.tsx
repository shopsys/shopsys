import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { FillIcon } from 'components/Basic/Icon/FillIcon';
import { StarIcon } from 'components/Basic/Icon/StarIcon';
import { Image } from 'components/Basic/Image/Image';
import { AdditionalServiceSummaryList } from 'components/Blocks/Product/AdditionalServices/AdditionalServiceSummaryList';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapOrderItemAdditionalServiceSummaryLines } from 'utils/mappers/additionalServices';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import {
    WRITE_REVIEW_ORDER_HASH_QUERY_PARAMETER_NAME,
    WRITE_REVIEW_PRODUCT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { twMergeCustom } from 'utils/twMerge';

type OrderDetailOrderItemProps = {
    orderItem: TypeOrderDetailItemFragment;
    orderUuid: string;
    orderUrlHash: string;
    productReviewsAllowed: boolean;
    reviewedProductUuids: Set<string>;
    isReviewAvailabilityLoading: boolean;
    isDiscount?: boolean;
    isOrderFromRegisteredCustomer: boolean;
};

export const OrderDetailOrderItem: FC<OrderDetailOrderItemProps> = ({
    orderItem,
    orderUuid,
    orderUrlHash,
    productReviewsAllowed,
    reviewedProductUuids,
    isReviewAvailabilityLoading,
    isDiscount,
    isOrderFromRegisteredCustomer,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { canCreateComplaint } = useAuthorization();
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const isProductGift = orderItem.type === TypeOrderItemTypeEnum.ProductGift;
    const showComplaintButton =
        canCreateComplaint &&
        isUserLoggedIn &&
        isOrderFromRegisteredCustomer &&
        orderItem.order.withdrawalRequest === null &&
        orderItem.type === TypeOrderItemTypeEnum.Product;
    const canShowProductReviewAction =
        !isReviewAvailabilityLoading &&
        settingsData?.settings?.productReviewsEnabled === true &&
        productReviewsAllowed &&
        orderItem.type === TypeOrderItemTypeEnum.Product &&
        !!orderItem.product?.isVisible;
    const hasAlreadyReviewed =
        canShowProductReviewAction && orderItem.product !== null && reviewedProductUuids.has(orderItem.product.uuid);
    const showWriteReviewButton = canShowProductReviewAction && !hasAlreadyReviewed;

    const getWriteReviewUrl = (): string => {
        const writeReviewQueryParams = new URLSearchParams({
            [WRITE_REVIEW_PRODUCT_QUERY_PARAMETER_NAME]: orderItem.product?.uuid ?? '',
        });

        if (!isUserLoggedIn) {
            writeReviewQueryParams.set(WRITE_REVIEW_ORDER_HASH_QUERY_PARAMETER_NAME, orderUrlHash);
        }

        return `${orderItem.product?.slug}?${writeReviewQueryParams.toString()}`;
    };

    const additionalServiceLines = mapOrderItemAdditionalServiceSummaryLines(orderItem.relatedItems, formatPrice);

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
                'font-secondary font-semibold first:border-none',
                'border-t border-t-border-default py-5 first:pt-0 last:pb-0',
            )}
        >
            <div className="relative flex items-center gap-3 vl:gap-5">
                {isProductGift && <GiftBadge className="top-5 rounded-tl-md" />}

                <div
                    className={twMergeCustom(
                        'flex vl:grid w-full vl:grid-cols-[3fr_2fr_1fr_2fr] flex-wrap items-center justify-between gap-3 vl:gap-5 border-b last:border-none',
                    )}
                >
                    <div className="flex vl:w-auto w-full items-center gap-5">
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
                                    className="inline-flex cursor-pointer items-center self-baseline whitespace-nowrap rounded-sm text-link-default text-sm underline outline-hidden hover:text-link-hovered"
                                    data-tid={TIDs.order_detail_create_complaint_button}
                                    tabIndex={0}
                                    aria-label={t('Create complaint for product {{ productName }}', {
                                        ns: 'accessibility',
                                        productName: orderItem.name,
                                    })}
                                    onClick={(e) => openCreateComplaintPopup(e, orderUuid, orderItem)}
                                >
                                    <FillIcon className="mr-1 size-5" />
                                    {t('Create complaint')}
                                </button>
                            )}

                            {showWriteReviewButton && (
                                <ExtendedNextLink
                                    className="inline-flex items-center self-baseline whitespace-nowrap rounded-sm text-link-default text-sm hover:text-link-hovered"
                                    href={getWriteReviewUrl()}
                                    tid={TIDs.order_detail_write_review_button}
                                    skeletonType="product"
                                    aria-label={t('Write a review for product {{ productName }}', {
                                        ns: 'accessibility',
                                        productName: orderItem.name,
                                    })}
                                >
                                    <StarIcon className="mr-1 size-5" />
                                    {t('Write a review')}
                                </ExtendedNextLink>
                            )}

                            {hasAlreadyReviewed && (
                                <p className="inline-flex items-center self-baseline whitespace-nowrap text-sm text-text-less">
                                    <StarIcon aria-hidden className="mr-1 size-5" />
                                    {t('Already reviewed.')}
                                </p>
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

            <AdditionalServiceSummaryList
                showHeading
                className="mt-3 vl:ml-25 border-border-less border-t pt-3"
                isPriceHighlighted={false}
                services={additionalServiceLines}
            />
        </div>
    );
};
