import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';

type CartInHeaderListItemProps = {
    cartItem: TypeCartItemFragment;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
    isRemovingFromCart: boolean;
};

export const CartInHeaderListItem: FC<CartInHeaderListItemProps> = ({
    cartItem: { product, uuid, quantity, type },
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
            className="relative flex h-auto w-full flex-row flex-wrap items-center gap-x-6 border-border-less border-b py-3 last:border-b-[3px] lg:flex-nowrap"
        >
            <div className="flex min-h-20 w-full flex-row items-center gap-x-6">
                {hasProductDetailLink ? (
                    <>
                        <ExtendedNextLink
                            className="flex w-20 items-center justify-center"
                            href={productSlug}
                            tabIndex={-1}
                            tid={TIDs.header_cart_list_item_image}
                            title={t('Go to product page')}
                            type="product"
                            aria-label={t('Go to product page of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                        >
                            <Image
                                alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                                className="size-20 object-contain"
                                height={80}
                                src={product.mainImage?.url}
                                width={80}
                            />
                        </ExtendedNextLink>

                        <ExtendedNextLink
                            className="flex-1 cursor-pointer font-secondary font-semibold text-sm text-text-default no-underline outline-hidden hover:text-link-default hover:underline"
                            href={productSlug}
                            tabIndex={0}
                            title={t('Go to product page')}
                            type="product"
                            aria-label={t('Go to product page of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                        >
                            {product.fullName}
                        </ExtendedNextLink>
                    </>
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
            </div>
            <div className="mt-2 flex flex-row gap-x-6 lg:mt-0 lg:w-auto">
                <div className="w-20 text-center font-secondary font-semibold text-sm">
                    {`${quantity} ${product.unit.name}`}
                </div>

                {isProduct && isPriceVisible(product.price.priceWithVat) && (
                    <div className="wrap-break-word w-28 font-bold font-secondary text-price-default lg:text-right">
                        {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                    </div>
                )}

                {isProductGift && isPriceVisible(product.giftPrice.priceWithVat) && (
                    <div className="wrap-break-word w-28 font-bold font-secondary text-price-default lg:text-right">
                        {formatPrice(mapPriceForCalculations(product.giftPrice.priceWithVat) * quantity)}
                    </div>
                )}
            </div>
            <RemoveCartItemButton
                ariaLabel={t(`Remove from cart ${product.fullName}`, { ns: 'accessibility' })}
                className="absolute top-2 right-0 cursor-pointer text-icon-less hover:text-icon-default lg:relative lg:top-0 lg:right-0"
                disabled={isRemovingFromCart}
                title={t('Remove from cart')}
                onRemoveFromCart={onRemoveFromCart}
            />
        </li>
    );
};
