import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler, useEffect, useRef, useState } from 'react';
import { AddToCart } from 'utils/cart/useAddToCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';
import { useDebounce } from 'utils/useDebounce';

type CartListItemProps = {
    item: TypeCartItemFragment;
    listIndex: number;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
    onAddToCart: AddToCart;
};

export const CartListItem: FC<CartListItemProps> = ({
    item: { product, quantity, uuid },
    listIndex,
    onRemoveFromCart,
    onAddToCart,
}) => {
    const spinboxRef = useRef<HTMLInputElement>(null);
    const [spinboxValue, setSpinboxValue] = useState<number>();
    const debouncedSpinboxValue = useDebounce(spinboxValue, 500);
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    useEffect(() => {
        if (debouncedSpinboxValue !== undefined && spinboxRef.current?.valueAsNumber !== quantity) {
            onAddToCart(product.uuid, debouncedSpinboxValue, listIndex, true);
        }
    }, [debouncedSpinboxValue]);

    useEffect(() => {
        if (quantity > 0 && spinboxRef.current) {
            spinboxRef.current.valueAsNumber = quantity;
        }
    }, [quantity]);

    return (
        <li>
            <section
                aria-labelledby={`product-${uuid}-name`}
                className="bg-background-more vl:flex-nowrap vl:p-5 relative flex flex-row flex-wrap items-center justify-between gap-4 rounded-xl p-4"
                data-tid={TIDs.pages_cart_list_item_ + product.catalogNumber}
            >
                <div className="vl:basis-auto vl:items-center vl:pr-0 vl:pt-0 flex basis-full gap-2.5 pt-6 pr-8">
                    <div className="flex size-20 shrink-0">
                        <ExtendedNextLink
                            className="relative isolate"
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
                    </div>

                    <div className="vl:flex-1 vl:flex-row vl:items-center vl:gap-8 flex flex-col items-start gap-2 xl:gap-16">
                        <div
                            className="vl:w-48 flex flex-col gap-2 tracking-wide"
                            data-tid={TIDs.pages_cart_list_item_name}
                        >
                            <ExtendedNextLink
                                className="font-secondary text-text-default hover:text-text-accent font-semibold no-underline hover:underline"
                                href={productSlug}
                                type="product"
                            >
                                <h3 className="text-sm lg:text-sm" id={`product-${uuid}-name`}>
                                    {product.fullName}
                                </h3>
                            </ExtendedNextLink>

                            <div className="text-text-less text-sm">
                                {t('Code')}: {product.catalogNumber}
                            </div>
                        </div>

                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            className="xs:w-44 flex-1"
                            isInquiryType={product.isInquiryType}
                        />
                    </div>
                </div>

                <div className="vl:flex-row vl:items-center vl:gap-8 flex w-auto flex-col justify-between gap-2 xl:gap-16">
                    <Spinbox
                        defaultValue={quantity}
                        id={uuid}
                        min={1}
                        ref={spinboxRef}
                        size="large"
                        step={1}
                        onChangeValueCallback={setSpinboxValue}
                    />

                    {isPriceVisible(product.price.priceWithVat) && (
                        <div className="font-secondary vl:w-40 whitespace-nowrap">
                            <span className="font-semibold">{formatPrice(product.price.priceWithVat)}</span>
                            <span className="text-text-less text-sm">&nbsp;/&nbsp;{product.unit.name}</span>
                        </div>
                    )}
                </div>

                <CartItemPrice productPrice={product.price} quantity={quantity} />

                <RemoveCartItemButton
                    ariaLabel={t('Remove product {{ productName }} from cart', { productName: product.fullName })}
                    className="vl:static text-icon-less hover:text-icon-default absolute top-2.5 right-2.5 flex cursor-pointer items-center rounded-md outline-none"
                    title={t('Remove product from cart')}
                    onRemoveFromCart={onRemoveFromCart}
                />
            </section>
        </li>
    );
};
