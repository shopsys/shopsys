import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { Image } from 'components/Basic/Image/Image';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

    return (
        <li
            key={uuid}
            data-tid={TIDs.header_cart_list_item_ + product.catalogNumber}
            className="relative flex h-auto w-full flex-col items-center gap-x-6 gap-y-2 border-border-less border-b py-4 first:pt-0 last:border-b-0"
        >
            <div className="relative flex min-h-20 w-full flex-row items-center gap-x-6">
                {isProductGift && <GiftBadge />}

                {hasProductDetailLink ? (
                    <ExtendedNextLink
                        className="group/product-link flex flex-1 cursor-pointer items-center gap-x-6 text-text-default no-underline outline-hidden hover:text-text-default hover:no-underline"
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

                        <span className="flex-1 font-secondary font-semibold text-sm group-hover/product-link:underline group-focus-visible/product-link:underline">
                            {product.fullName}
                        </span>
                    </ExtendedNextLink>
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

                        <span className="flex-1 font-secondary font-semibold text-sm text-text-default">
                            {product.fullName}
                        </span>
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
                        {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
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
