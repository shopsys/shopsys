import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';
import { isPriceVisible } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';

type CartInHeaderListItemProps = {
    cartItem: TypeCartItemFragment;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
};

export const CartInHeaderListItem: FC<CartInHeaderListItemProps> = ({
    cartItem: { product, uuid, quantity },
    onRemoveFromCart,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    return (
        <li
            key={uuid}
            className="border-border-less relative flex h-auto w-full flex-row flex-wrap items-center gap-x-6 border-b py-3 last:border-b-[3px] lg:flex-nowrap"
        >
            <div className="flex min-h-20 w-full flex-row items-center gap-x-6">
                <ExtendedNextLink
                    aria-label={t('Go to product page of {{ productName }}', { productName: product.fullName })}
                    className="flex w-20 items-center justify-center"
                    href={productSlug}
                    tabIndex={-1}
                    tid={TIDs.header_cart_list_item_image}
                    title={t('Go to product page')}
                    type="product"
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
                    aria-label={t('Go to product page ' + product.fullName)}
                    className="font-secondary text-text-default hover:text-link-default flex-1 cursor-pointer text-sm font-semibold no-underline outline-hidden hover:underline"
                    href={productSlug}
                    tabIndex={0}
                    title={t('Go to product page')}
                    type="product"
                >
                    {product.fullName}
                </ExtendedNextLink>
            </div>
            <div className="mt-2 flex flex-row gap-x-6 lg:mt-0 lg:w-auto">
                <div className="font-secondary w-20 text-center text-sm font-semibold">
                    {quantity + ' ' + product.unit.name}
                </div>

                {isPriceVisible(product.price.priceWithVat) && (
                    <div className="font-secondary text-price-default w-28 font-bold break-words lg:text-right">
                        {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                    </div>
                )}
            </div>
            <RemoveCartItemButton
                ariaLabel={t('Remove from cart ' + product.fullName)}
                className="text-icon-less hover:text-icon-default absolute top-2 right-0 cursor-pointer lg:relative lg:top-0 lg:right-0"
                title={t('Remove from cart')}
                onRemoveFromCart={onRemoveFromCart}
            />
        </li>
    );
};
