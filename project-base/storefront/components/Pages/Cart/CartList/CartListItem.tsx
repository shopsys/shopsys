import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { IconButton } from 'components/Forms/Button/IconButton';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { AddToCart } from 'utils/cart/useAddToCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';
import { useDebounce } from 'utils/useDebounce';

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
    const lastSubmittedQuantityRef = useRef<number | null>(null);
    const [spinboxValue, setSpinboxValue] = useState<number>();
    const debouncedSpinboxValue = useDebounce(spinboxValue, 500);
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

    const onSubmitCartChange = useEffectEvent((productUuid: string, qty: number, idx: number) => {
        if (qty === quantity) {
            lastSubmittedQuantityRef.current = null;
            return;
        }
        if (lastSubmittedQuantityRef.current === qty) {
            return;
        }
        lastSubmittedQuantityRef.current = qty;
        onAddToCart(productUuid, qty, idx, true);
    });

    useEffect(() => {
        if (debouncedSpinboxValue === undefined) {
            return;
        }

        onSubmitCartChange(product.uuid, debouncedSpinboxValue, listIndex);
    }, [debouncedSpinboxValue, listIndex, product.uuid]);

    useEffect(() => {
        if (quantity > 0 && spinboxRef.current) {
            spinboxRef.current.valueAsNumber = quantity;
        }
    }, [quantity]);

    return (
        <li>
            <section
                aria-describedby={cartItemDescriptionId}
                aria-labelledby={`product-${uuid}-name`}
                className="relative flex flex-row flex-wrap vl:flex-nowrap items-center justify-between gap-4 rounded-xl bg-background-more p-4 vl:p-5"
                data-tid={TIDs.pages_cart_list_item_ + product.catalogNumber}
            >
                <p className="sr-only" id={cartItemDescriptionId}>
                    {cartItemSummary}
                </p>

                {isProductGift && <GiftBadge />}

                <div className="flex basis-full vl:basis-auto vl:items-center gap-2.5 pt-6 vl:pt-0 pr-8 vl:pr-0">
                    <div className="flex size-20 shrink-0">
                        {hasProductDetailLink ? (
                            <ExtendedNextLink
                                className="relative"
                                href={productSlug}
                                tabIndex={-1}
                                tid={TIDs.cart_list_item_image}
                                type="product"
                            >
                                <Image
                                    alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                                    className="size-20 object-contain mix-blend-multiply"
                                    height={80}
                                    src={product.mainImage?.url}
                                    width={80}
                                />
                            </ExtendedNextLink>
                        ) : (
                            <Image
                                alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                                className="size-20 object-contain mix-blend-multiply"
                                height={80}
                                src={product.mainImage?.url}
                                width={80}
                            />
                        )}
                    </div>

                    <div className="flex vl:flex-1 vl:flex-row flex-col items-start vl:items-center gap-2 vl:gap-8 xl:gap-16">
                        <div
                            className="flex vl:w-48 flex-col gap-2 tracking-wide"
                            data-tid={TIDs.pages_cart_list_item_name}
                        >
                            {hasProductDetailLink ? (
                                <ExtendedNextLink
                                    className="font-secondary font-semibold text-text-default no-underline hover:text-text-accent hover:underline"
                                    href={productSlug}
                                    type="product"
                                >
                                    <h3 className="text-sm lg:text-sm" id={`product-${uuid}-name`}>
                                        {product.fullName}
                                    </h3>
                                </ExtendedNextLink>
                            ) : (
                                <h3
                                    className="font-secondary font-semibold text-sm text-text-default lg:text-sm"
                                    id={`product-${uuid}-name`}
                                >
                                    {product.fullName}
                                </h3>
                            )}

                            <div className="text-sm text-text-less">
                                {t('Code')}: {product.catalogNumber}
                            </div>

                            {product.promotionBuyQuantity !== null && product.promotionFreeQuantity !== null && (
                                <div className="text-text-less text-xs">
                                    {t('Promotion {{ x }} + {{ y }} free', {
                                        x: product.promotionBuyQuantity,
                                        y: product.promotionFreeQuantity,
                                    })}
                                </div>
                            )}
                        </div>

                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            className="xs:w-44 flex-1"
                            isInquiryType={product.isInquiryType}
                        />
                    </div>
                </div>

                <div className="flex w-auto vl:flex-row flex-col vl:items-center justify-between gap-2 vl:gap-8 xl:gap-16">
                    {isProduct ? (
                        <Spinbox
                            defaultValue={quantity}
                            id={uuid}
                            max={product.isAllowedNegativeStock ? null : product.stockQuantity}
                            min={1}
                            ref={spinboxRef}
                            size="large"
                            step={1}
                            ariaDescription={t(
                                'Type in a quantity for {{ productName }} or use the buttons to change it.',
                                {
                                    ns: 'accessibility',
                                    productName: product.fullName,
                                },
                            )}
                            ariaLabel={t('Quantity for {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            decreaseAriaLabel={t('Decrease quantity of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            getValueAnnouncement={(currentValue) =>
                                t(
                                    'Quantity for {{ productName }} changed to {{ quantity }} {{ unit }}. Item total with VAT: {{ price }}.',
                                    {
                                        ns: 'accessibility',
                                        productName: product.fullName,
                                        quantity: currentValue,
                                        unit: product.unit.name,
                                        price: formatPrice(
                                            mapPriceForCalculations(product.price.priceWithVat) * currentValue,
                                        ),
                                    },
                                )
                            }
                            increaseAriaLabel={t('Increase quantity of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            onChangeValueCallback={setSpinboxValue}
                        />
                    ) : (
                        <div className="min-w-[100px] text-center">{quantity}</div>
                    )}

                    {isProduct && isPriceVisible(product.price.priceWithVat) && (
                        <div className="vl:w-40 whitespace-nowrap font-secondary">
                            <span className="font-semibold">{formatPrice(product.price.priceWithVat)}</span>
                            <span className="text-sm text-text-less">&nbsp;/&nbsp;{product.unit.name}</span>
                        </div>
                    )}
                    {isProductGift && isPriceVisible(product.giftPrice.priceWithVat) && (
                        <div className="vl:w-40 whitespace-nowrap font-secondary">
                            <span className="font-semibold">{formatPrice(product.giftPrice.priceWithVat)}</span>
                            <span className="text-sm text-text-less">&nbsp;/&nbsp;{product.unit.name}</span>
                        </div>
                    )}
                </div>

                {isProduct && (
                    <CartItemPrice freeQuantity={freeQuantity} productPrice={product.price} quantity={quantity} />
                )}
                {isProductGift && (
                    <CartItemPrice freeQuantity={0} productPrice={product.giftPrice} quantity={quantity} />
                )}

                <IconButton
                    Icon={RemoveIcon}
                    className="vl:static absolute top-2.5 right-2.5 flex items-center"
                    disabled={isRemovingFromCart}
                    tid={TIDs.pages_cart_removecartitembutton}
                    title={t('Remove product from cart')}
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
