import { GiftIcon } from 'components/Basic/Icon/GiftIcon';
import { Image } from 'components/Basic/Image/Image';
import { CartItemPartialAvailability } from 'components/Blocks/Product/CartItemPartialAvailability';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRestoreCartGiftMutation } from 'graphql/requests/cart/mutations/RestoreCartGiftMutation.generated';
import { useEffect, useRef, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { CartProductGift } from 'utils/cart/groupCartItemsWithGifts';
import { isCartItemPartiallyAvailable } from 'utils/cart/isCartItemPartiallyAvailable';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

type CartProductGiftsProps = {
    gifts: CartProductGift[];
    isRemovingGift: boolean;
    onRemoveGift: (cartItem: TypeCartItemFragment, listIndex: number) => void;
    parentCartItemUuid: string;
};

export const CartProductGifts: FC<CartProductGiftsProps> = ({
    gifts,
    parentCartItemUuid,
    isRemovingGift,
    onRemoveGift,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { domainId } = useDomainConfig();
    const [{ fetching: isRestoringGift }, restoreCartGiftMutation] = useRestoreCartGiftMutation();

    const [restoringGiftUuid, setRestoringGiftUuid] = useState<string | null>(null);
    const checkboxToRefocus = useRef<HTMLInputElement | null>(null);

    const restoreGift = async (giftProductUuid: string) => {
        if (restoringGiftUuid !== null) {
            return;
        }

        setRestoringGiftUuid(giftProductUuid);

        try {
            const result = await restoreCartGiftMutation({ input: { cartUuid, giftProductUuid } });

            if (result.data?.RestoreCartGift) {
                dispatchBroadcastChannel('refetchCart', domainId);
            }
        } finally {
            setRestoringGiftUuid(null);
        }
    };

    const isDisabled = isRemovingGift || isRestoringGift || restoringGiftUuid !== null;

    useEffect(() => {
        if (isDisabled) {
            return;
        }

        const checkbox = checkboxToRefocus.current;
        checkboxToRefocus.current = null;

        if (checkbox?.isConnected && document.activeElement === document.body) {
            checkbox.focus({ preventScroll: true });
        }
    }, [isDisabled]);

    if (gifts.length === 0) {
        return null;
    }

    return (
        <section
            aria-labelledby={`product-gifts-${parentCartItemUuid}-heading`}
            className="vl:col-span-full mt-4 vl:grid vl:grid-cols-subgrid border-border-less border-t pt-4"
        >
            <h4
                className="vl:col-span-full mb-3 flex items-center gap-2 vl:pl-22.5 font-secondary font-semibold text-sm text-text-less"
                id={`product-gifts-${parentCartItemUuid}-heading`}
            >
                <GiftIcon aria-hidden="true" className="size-4 shrink-0 text-text-less" />
                {t('Gift with purchase')}
            </h4>
            <ul className="vl:col-span-full flex vl:grid vl:grid-cols-subgrid flex-col gap-4 vl:gap-x-[inherit]">
                {gifts.map((giftItem) => {
                    const gift = giftItem.state === 'added' ? giftItem.cartItem.product : giftItem.removedGift.product;
                    const quantity =
                        giftItem.state === 'added' ? giftItem.cartItem.quantity : giftItem.removedGift.quantity;
                    const image =
                        giftItem.state === 'added'
                            ? giftItem.cartItem.product.mainImage
                            : giftItem.removedGift.product.images[0];

                    const addedProduct = giftItem.state === 'added' ? giftItem.cartItem.product : null;
                    const checkboxId = `cart-gift-${parentCartItemUuid}-${gift.uuid}`;
                    const showUnitPrice =
                        isPriceVisible(gift.giftPrice.priceWithVat) &&
                        mapPriceForCalculations(gift.giftPrice.priceWithVat) > 0;

                    return (
                        <li
                            key={gift.uuid}
                            data-tid={TIDs.pages_cart_gift_ + gift.uuid}
                            aria-busy={restoringGiftUuid === gift.uuid}
                            className={twMergeCustom(
                                'vl:col-span-full grid min-w-0 grid-cols-[minmax(0,1fr)] items-center gap-y-1',
                                giftItem.state === 'added' && 'vl:grid-cols-subgrid vl:gap-x-[inherit]',
                            )}
                        >
                            <div className="vl:col-start-1 flex min-w-0 items-center gap-2 vl:pl-22.5 [&>div:first-child]:w-fit [&>div:first-child]:shrink-0 [&>div:first-child]:self-stretch">
                                <Checkbox
                                    disabled={isDisabled}
                                    id={checkboxId}
                                    label={<span aria-hidden="true" />}
                                    labelWrapperClassName="h-full min-h-11 w-fit items-center gap-0 font-normal text-text-default hover:text-text-default"
                                    name={checkboxId}
                                    value={giftItem.state === 'added'}
                                    onChange={(event) => {
                                        checkboxToRefocus.current =
                                            document.activeElement === event.currentTarget ? event.currentTarget : null;

                                        if (giftItem.state === 'added') {
                                            onRemoveGift(giftItem.cartItem, giftItem.listIndex);
                                        } else {
                                            void restoreGift(gift.uuid);
                                        }
                                    }}
                                />
                                <label
                                    htmlFor={checkboxId}
                                    className={twMergeCustom(
                                        'flex min-h-11 shrink-0 cursor-pointer items-center',
                                        isDisabled && 'cursor-no-drop opacity-50',
                                    )}
                                >
                                    <Image
                                        alt=""
                                        className="size-14 object-contain mix-blend-multiply"
                                        height={56}
                                        src={image?.url}
                                        width={56}
                                    />
                                </label>
                                <div className="min-w-0 font-secondary text-sm">
                                    <label
                                        data-tid={TIDs.pages_cart_gift_checkbox_label}
                                        htmlFor={checkboxId}
                                        className={twMergeCustom(
                                            'wrap-break-words block w-fit max-w-full cursor-pointer font-semibold',
                                            isDisabled && 'cursor-no-drop opacity-50',
                                        )}
                                    >
                                        {gift.fullName}
                                        {giftItem.state === 'removed' && showUnitPrice && (
                                            <span className="block whitespace-nowrap md:ml-2 md:inline">
                                                + {formatPrice(gift.giftPrice.priceWithVat)}{' '}
                                                <span className="font-normal text-text-less">/ {gift.unit.name}</span>
                                            </span>
                                        )}
                                    </label>
                                    {addedProduct &&
                                        (isCartItemPartiallyAvailable(addedProduct, quantity) ? (
                                            <CartItemPartialAvailability
                                                className="mt-1 text-xs"
                                                expectedRestockingDate={addedProduct.expectedRestockingDate}
                                                stockQuantity={addedProduct.stockQuantity ?? 0}
                                                unitName={addedProduct.unit.name}
                                            />
                                        ) : (
                                            <ProductAvailability
                                                availability={addedProduct.availability}
                                                availableStoresCount={addedProduct.availableStoresCount}
                                                className="mt-1"
                                                displayMode="inline"
                                                isInquiryType={addedProduct.isInquiryType}
                                                isPersonalPickupOnly={addedProduct.isPersonalPickupOnly}
                                            />
                                        ))}
                                    {giftItem.relatedProducts.length > 1 && (
                                        <p className="mt-1 text-text-less text-xs">
                                            {t('Gift for: {{ products }}', {
                                                products: giftItem.relatedProducts
                                                    .map((product) => product.fullName)
                                                    .join(', '),
                                            })}
                                        </p>
                                    )}
                                </div>
                            </div>
                            {giftItem.state === 'added' && (
                                <div className="flex vl:contents flex-wrap items-center justify-between gap-x-3 gap-y-1 pl-23">
                                    <div className="vl:col-start-2">
                                        <span className="vl:hidden text-sm text-text-less tabular-nums">
                                            {showUnitPrice ? (
                                                <>
                                                    {quantity} × {formatPrice(gift.giftPrice.priceWithVat)} /{' '}
                                                    {gift.unit.name}
                                                </>
                                            ) : (
                                                <>
                                                    {quantity} {gift.unit.name}
                                                </>
                                            )}
                                        </span>
                                        <span className="vl:block hidden min-w-35 text-center">{quantity}</span>
                                    </div>
                                    {showUnitPrice && (
                                        <div className="vl:col-start-3 vl:block hidden whitespace-nowrap font-secondary">
                                            <span className="font-semibold">
                                                {formatPrice(gift.giftPrice.priceWithVat)}
                                            </span>
                                            <span className="text-sm text-text-less">
                                                &nbsp;/&nbsp;{gift.unit.name}
                                            </span>
                                        </div>
                                    )}
                                    <CartItemPrice
                                        className="vl:col-start-4 vl:row-start-1"
                                        freeQuantity={0}
                                        productPrice={gift.giftPrice}
                                        quantity={quantity}
                                    />
                                </div>
                            )}
                        </li>
                    );
                })}
            </ul>
        </section>
    );
};
