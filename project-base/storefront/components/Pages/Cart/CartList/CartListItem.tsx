import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { Image } from 'components/Basic/Image/Image';
import { CartItemPartialAvailability } from 'components/Blocks/Product/CartItemPartialAvailability';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { IconButton } from 'components/Forms/Button/IconButton';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { useCallback, useEffect, useRef, useState } from 'react';
import { isCartItemPartiallyAvailable } from 'utils/cart/isCartItemPartiallyAvailable';
import { AddToCart } from 'utils/cart/useAddToCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';

type CartListItemProps = {
    item: TypeCartItemFragment;
    listIndex: number;
    onRemoveFromCart: () => void;
    onAddToCart: AddToCart;
    isRemovingFromCart: boolean;
};

export const CartListItem: FC<CartListItemProps> = ({
    item: { product, quantity, uuid, type, freeQuantity },
    listIndex,
    onRemoveFromCart,
    onAddToCart,
    isRemovingFromCart,
}) => {
    const spinboxRef = useRef<HTMLInputElement>(null);
    const confirmedQuantityRef = useRef(quantity);
    const inFlightQuantityRef = useRef<number | null>(null);
    const lastSubmittedQuantityRef = useRef<number | null>(null);
    const queuedQuantityRef = useRef<number | null>(null);
    const [visibleQuantity, setVisibleQuantity] = useState(quantity);
    const [quantityChangeAnnouncement, setQuantityChangeAnnouncement] = useState('');
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant?.slug : product.slug;
    const hasProductDetailLink = productSlug !== undefined;
    const isProduct = type === TypeCartItemTypeEnum.Product;
    const isProductGift = type === TypeCartItemTypeEnum.ProductGift;
    const productPrice = isProductGift ? product.giftPrice : product.price;
    const payableQuantity = Math.max(quantity - freeQuantity, 0);
    const itemTotalPriceWithVat = formatPrice(mapPriceForCalculations(productPrice.priceWithVat) * payableQuantity);
    const itemTotalPriceWithoutVat = formatPrice(
        mapPriceForCalculations(productPrice.priceWithoutVat) * payableQuantity,
    );
    const cartItemDescriptionId = `product-${uuid}-cart-summary`;
    const cartItemSummary = [
        t('Code: {{ catalogNumber }}.', { ns: 'accessibility', catalogNumber: product.catalogNumber }),
        `${t('Availability')}: ${product.availability.name}.`,
        t('Quantity: {{ quantity }} {{ unit }}.', {
            ns: 'accessibility',
            quantity,
            unit: product.unit.name,
        }),
        isPriceVisible(productPrice.priceWithVat)
            ? t('Unit price with VAT: {{ price }}.', {
                  ns: 'accessibility',
                  price: formatPrice(productPrice.priceWithVat),
              })
            : undefined,
        isPriceVisible(productPrice.priceWithVat)
            ? t('Item total with VAT: {{ price }}.', { ns: 'accessibility', price: itemTotalPriceWithVat })
            : undefined,
        isPriceVisible(productPrice.priceWithoutVat)
            ? t('Item total without VAT: {{ price }}.', { ns: 'accessibility', price: itemTotalPriceWithoutVat })
            : undefined,
        freeQuantity > 0
            ? t('{{ quantity }} {{ unit }} is free.', {
                  ns: 'accessibility',
                  quantity: freeQuantity,
                  unit: product.unit.name,
              })
            : undefined,
    ]
        .filter(Boolean)
        .join(' ');

    const restorePreviousQuantity = useCallback((quantity: number) => {
        setVisibleQuantity(quantity);

        if (spinboxRef.current) {
            spinboxRef.current.valueAsNumber = quantity;
        }
    }, []);

    const onSubmitCartChange = useCallback(
        async (requestedQuantity: number) => {
            if (inFlightQuantityRef.current !== null) {
                queuedQuantityRef.current = requestedQuantity;

                return;
            }

            let quantityToSubmit: number | null = requestedQuantity;

            while (quantityToSubmit !== null) {
                const quantityToCompare = lastSubmittedQuantityRef.current ?? confirmedQuantityRef.current;

                if (quantityToSubmit === quantityToCompare) {
                    quantityToSubmit = null;

                    continue;
                }

                const submittedQuantity = quantityToSubmit;

                inFlightQuantityRef.current = submittedQuantity;
                lastSubmittedQuantityRef.current = submittedQuantity;
                queuedQuantityRef.current = null;

                const addToCartResult = await onAddToCart(product.uuid, submittedQuantity, listIndex, true);

                inFlightQuantityRef.current = null;

                if (!addToCartResult) {
                    restorePreviousQuantity(confirmedQuantityRef.current);
                    lastSubmittedQuantityRef.current = null;
                    queuedQuantityRef.current = null;

                    return;
                }

                const confirmedQuantity = addToCartResult.addProductResult.cartItem.quantity;

                confirmedQuantityRef.current = confirmedQuantity;
                lastSubmittedQuantityRef.current = confirmedQuantity;

                setQuantityChangeAnnouncement(
                    t('Quantity of {{ productName }} updated to {{ quantity }} {{ unit }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                        quantity: confirmedQuantity,
                        unit: product.unit.name,
                    }),
                );

                quantityToSubmit = queuedQuantityRef.current;
                queuedQuantityRef.current = null;

                if (quantityToSubmit === null) {
                    restorePreviousQuantity(confirmedQuantity);
                }
            }
        },
        [listIndex, onAddToCart, product.fullName, product.unit.name, product.uuid, restorePreviousQuantity, t],
    );

    useEffect(() => {
        if (lastSubmittedQuantityRef.current !== null && quantity !== lastSubmittedQuantityRef.current) {
            return;
        }

        confirmedQuantityRef.current = quantity;
        setVisibleQuantity(quantity);
        lastSubmittedQuantityRef.current = null;
    }, [quantity]);

    const productIdentityContent = (
        <>
            <div className="absolute inset-y-0 left-0 flex size-20 shrink-0" data-tid={TIDs.cart_list_item_image}>
                <Image
                    alt={
                        hasProductDetailLink
                            ? ''
                            : generateProductImageAlt(product.fullName, product.categories[0]?.name)
                    }
                    className="size-20 object-contain mix-blend-multiply"
                    height={80}
                    src={product.mainImage?.url}
                    width={80}
                />
            </div>

            <h3
                className="font-secondary font-semibold text-sm group-hover/product-link:underline lg:text-sm"
                data-tid={TIDs.pages_cart_list_item_name}
                id={`product-${uuid}-name`}
            >
                <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                    {product.fullName}
                </span>
            </h3>
        </>
    );

    return (
        <li>
            <section
                aria-describedby={cartItemDescriptionId}
                aria-labelledby={`product-${uuid}-name`}
                className="relative flex flex-row flex-wrap vl:flex-nowrap items-center justify-between gap-4 xxl:gap-10 rounded-xl bg-background-more p-4 vl:p-5 xl:grid xl:grid-cols-[minmax(32rem,1fr)_8.75rem_minmax(8rem,10rem)_minmax(8rem,max-content)_1.5rem] xl:justify-normal xl:gap-8"
                data-tid={TIDs.pages_cart_list_item_ + product.catalogNumber}
            >
                <p className="sr-only" id={cartItemDescriptionId}>
                    {cartItemSummary}
                </p>

                {isProductGift && <GiftBadge />}

                <div className="grid basis-full vl:basis-auto grid-cols-[5rem_minmax(0,1fr)] vl:grid-cols-[5rem_12rem_2rem_minmax(11rem,1fr)] vl:items-center gap-x-2.5 pt-6 vl:pt-0 pr-8 vl:pr-0 xl:col-start-1 xl:grid-cols-[5rem_minmax(0,1fr)_2.5rem_13rem]">
                    <div className="relative col-span-2 flex min-h-20 flex-col justify-center gap-2 pl-22.5">
                        {hasProductDetailLink ? (
                            <ExtendedNextLink
                                preventRedirectOnTextSelection
                                className="group/product-link select-text text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
                                data-focus-color="preserve"
                                draggable={false}
                                href={productSlug}
                                type="product"
                                aria-label={t('Go to product page of {{ productName }}', {
                                    ns: 'accessibility',
                                    productName: product.fullName,
                                })}
                            >
                                {productIdentityContent}
                            </ExtendedNextLink>
                        ) : (
                            <div>{productIdentityContent}</div>
                        )}

                        <div className="text-sm text-text-less">
                            {t('Code')}: {product.catalogNumber}
                        </div>
                    </div>

                    {product.promotionBuyQuantity !== null && product.promotionFreeQuantity !== null && (
                        <div className="col-start-2 text-text-less text-xs">
                            {t('Promotion {{ x }} + {{ y }} free', {
                                x: product.promotionBuyQuantity,
                                y: product.promotionFreeQuantity,
                            })}
                        </div>
                    )}

                    {isCartItemPartiallyAvailable(product, quantity) ? (
                        <CartItemPartialAvailability
                            className="col-start-2 vl:col-start-4 vl:row-span-3 vl:row-start-1 vl:w-44 min-w-0 vl:self-center text-sm xl:w-52"
                            expectedRestockingDate={product.expectedRestockingDate}
                            stockQuantity={product.stockQuantity ?? 0}
                            unitName={product.unit.name}
                        />
                    ) : (
                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            isPersonalPickupOnly={product.isPersonalPickupOnly}
                            className="col-start-2 vl:col-start-4 vl:row-span-3 vl:row-start-1 vl:w-44 min-w-0 vl:self-center xl:w-52"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </div>

                <div className="flex vl:flex-row flex-col vl:items-center justify-between gap-2 vl:gap-8 xl:contents">
                    {isProduct ? (
                        <Spinbox
                            defaultValue={visibleQuantity}
                            decreaseAriaLabel={t('Decrease quantity of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            id={uuid}
                            increaseAriaLabel={t('Increase quantity of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            inputAriaLabel={t('Quantity of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            liveAnnouncement={quantityChangeAnnouncement}
                            max={product.isAllowedNegativeStock ? null : product.stockQuantity}
                            min={1}
                            minValueDecreaseAriaLabel={t('Remove from cart product {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            minValueDecreaseIcon={<TrashCanIcon className="size-5" />}
                            minValueDecreaseTitle={t('Remove product from cart')}
                            ref={spinboxRef}
                            className="vl:w-35 w-40 xl:col-start-2"
                            step={1}
                            onChangeValueCallback={(requestedQuantity) => {
                                setVisibleQuantity(requestedQuantity);
                                onSubmitCartChange(requestedQuantity);
                            }}
                            onMinValueDecrease={onRemoveFromCart}
                        />
                    ) : (
                        <div className="min-w-35 text-center xl:col-start-2">{quantity}</div>
                    )}

                    {isProduct && isPriceVisible(product.price.priceWithVat) && (
                        <div className="vl:w-40 whitespace-nowrap font-secondary xl:col-start-3">
                            <span className="font-semibold">{formatPrice(product.price.priceWithVat)}</span>
                            <span className="text-sm text-text-less">&nbsp;/&nbsp;{product.unit.name}</span>
                        </div>
                    )}

                    {isProductGift && isPriceVisible(product.giftPrice.priceWithVat) && (
                        <div className="vl:w-40 whitespace-nowrap font-secondary xl:col-start-3">
                            <span className="font-semibold">{formatPrice(product.giftPrice.priceWithVat)}</span>
                            <span className="text-sm text-text-less">&nbsp;/&nbsp;{product.unit.name}</span>
                        </div>
                    )}
                </div>

                {isProduct && (
                    <CartItemPrice
                        className="xl:col-start-4"
                        freeQuantity={freeQuantity}
                        productPrice={product.price}
                        quantity={quantity}
                    />
                )}
                {isProductGift && (
                    <CartItemPrice
                        className="xl:col-start-4"
                        freeQuantity={0}
                        productPrice={product.giftPrice}
                        quantity={quantity}
                    />
                )}

                <IconButton
                    Icon={TrashCanIcon}
                    className="vl:static absolute top-2.5 right-2.5 flex items-center xl:col-start-5"
                    disabled={isRemovingFromCart}
                    shape="rounded"
                    size="small"
                    tid={TIDs.pages_cart_removecartitembutton}
                    title={t('Remove product from cart')}
                    tooltipLabel={t('Remove product from cart')}
                    tooltipPlacement="left"
                    variant="ghost"
                    ariaLabel={t('Remove from cart product {{ productName }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                    })}
                    onClick={onRemoveFromCart}
                />
            </section>
        </li>
    );
};
