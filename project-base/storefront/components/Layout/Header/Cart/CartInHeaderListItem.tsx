import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { Image } from 'components/Basic/Image/Image';
import { AdditionalServiceSummaryList } from 'components/Blocks/Product/AdditionalServices/AdditionalServiceSummaryList';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { MouseEventHandler, useState } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapCartItemAdditionalServiceSummaryLines } from 'utils/mappers/additionalServices';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';

type CartInHeaderListItemProps = {
    cartItem: TypeCartItemFragment;
    listIndex: number;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
    isRemovingFromCart: boolean;
};

export const CartInHeaderListItem: FC<CartInHeaderListItemProps> = ({
    cartItem,
    cartItem: { product, uuid, quantity, type },
    listIndex,
    onRemoveFromCart,
    isRemovingFromCart,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant?.slug : product.slug;
    const hasProductDetailLink = productSlug !== undefined;
    const isProduct = type === TypeCartItemTypeEnum.Product;
    const isProductGift = type === TypeCartItemTypeEnum.ProductGift;
    const areAdditionalServicePricesVisible = cartItem.additionalServices.every((additionalService) =>
        isPriceVisible(additionalService.price.priceWithVat),
    );
    const additionalServicesTotalPrice = cartItem.additionalServices.reduce(
        (total, additionalService) => total + mapPriceForCalculations(additionalService.price.priceWithVat) * quantity,
        0,
    );
    const hasAdditionalServices = isProduct && cartItem.additionalServices.length > 0;
    const productLinePrice =
        mapPriceForCalculations(product.price.priceWithVat) * quantity +
        (areAdditionalServicePricesVisible ? additionalServicesTotalPrice : 0);

    const [areAdditionalServicesShown, setAreAdditionalServicesShown] = useState(false);

    const additionalServicesToggle = hasAdditionalServices ? (
        <button
            aria-expanded={areAdditionalServicesShown}
            className="w-fit cursor-pointer text-left font-secondary text-link-default text-xs outline-hidden hover:text-link-hovered hover:underline"
            type="button"
            onClick={() => setAreAdditionalServicesShown((isShown) => !isShown)}
        >
            {t('+ {{ count }} additional services', { count: cartItem.additionalServices.length })}
        </button>
    ) : null;

    return (
        <li
            key={uuid}
            data-tid={TIDs.header_cart_list_item_ + product.catalogNumber}
            className="relative flex h-auto w-full flex-col items-center gap-x-6 gap-y-2 border-border-less border-b py-4 first:pt-0 last:border-b-0"
        >
            <div className="relative flex min-h-20 w-full flex-row items-center gap-x-6">
                {isProductGift && <GiftBadge />}

                {hasProductDetailLink ? (
                    <div className="flex min-w-0 flex-1 flex-col items-start gap-1">
                        <ExtendedNextLink
                            className="group/product-link flex w-full min-w-0 cursor-pointer items-center gap-x-6 text-text-default no-underline outline-hidden hover:text-text-default hover:no-underline"
                            href={productSlug}
                            type="product"
                            aria-label={t('Go to product page of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                        >
                            <div
                                className="flex w-20 shrink-0 items-center justify-center"
                                data-tid={TIDs.header_cart_list_item_image}
                            >
                                <Image
                                    alt=""
                                    className="size-20 object-contain"
                                    height={80}
                                    src={product.mainImage?.url}
                                    width={80}
                                />
                            </div>

                            <span className="min-w-0 flex-1 font-secondary font-semibold text-sm group-hover/product-link:underline group-focus-visible/product-link:underline">
                                {product.fullName}
                            </span>
                        </ExtendedNextLink>

                        <div className="pl-[6.5rem]">{additionalServicesToggle}</div>
                    </div>
                ) : (
                    <>
                        <div className="flex w-20 items-center justify-center">
                            <Image
                                alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                                className="size-20 object-contain"
                                height={80}
                                src={product.mainImage?.url}
                                width={80}
                            />
                        </div>

                        <div className="flex flex-1 flex-col items-start gap-1">
                            <span className="font-secondary font-semibold text-sm text-text-default">
                                {product.fullName}
                            </span>

                            {additionalServicesToggle}
                        </div>
                    </>
                )}

                {!isProductGift && (
                    <RemoveCartItemButton
                        ariaLabel={t('Remove from cart product {{ productName }}', {
                            ns: 'accessibility',
                            productName: product.fullName,
                        })}
                        className="cursor-pointer text-icon-less hover:text-icon-default"
                        disabled={isRemovingFromCart}
                        title={t('Remove from cart')}
                        onRemoveFromCart={onRemoveFromCart}
                    />
                )}
            </div>

            {hasAdditionalServices && areAdditionalServicesShown && (
                <div className="flex w-full flex-col gap-2">
                    <div className="flex items-center justify-between gap-2 text-sm text-text-less">
                        <span>{t('Product')}</span>
                        {isPriceVisible(product.price.priceWithVat) && (
                            <span className="whitespace-nowrap">
                                {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                            </span>
                        )}
                    </div>

                    <AdditionalServiceSummaryList
                        services={mapCartItemAdditionalServiceSummaryLines(
                            cartItem.additionalServices,
                            quantity,
                            product.unit.name,
                            formatPrice,
                        )}
                    />
                </div>
            )}

            <div className="flex w-full flex-row items-center justify-between gap-x-2">
                {isProduct ? (
                    <div className="w-40">
                        <CartItemQuantityControls
                            cartItem={cartItem}
                            gtmMessageOrigin={GtmMessageOriginType.cart}
                            gtmProductListName={GtmProductListNameType.cart}
                            listIndex={listIndex}
                            size="small"
                        />
                    </div>
                ) : (
                    <div className="font-secondary font-semibold text-sm">{`${quantity} ${product.unit.name}`}</div>
                )}

                {isProduct && isPriceVisible(product.price.priceWithVat) && (
                    <div className="wrap-break-word w-28 text-right font-bold font-secondary text-price-default">
                        {formatPrice(productLinePrice)}
                    </div>
                )}

                {isProductGift && isPriceVisible(product.giftPrice.priceWithVat) && (
                    <div className="wrap-break-word w-28 text-right font-bold font-secondary text-price-default">
                        {formatPrice(mapPriceForCalculations(product.giftPrice.priceWithVat) * quantity)}
                    </div>
                )}
            </div>
        </li>
    );
};
