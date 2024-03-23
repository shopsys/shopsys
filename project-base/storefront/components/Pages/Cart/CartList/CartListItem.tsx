import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { AddToCartAction } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler, useRef } from 'react';

type CartListItemProps = {
    item: CartItemFragment;
    listIndex: number;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
    onItemQuantityChange: AddToCartAction;
};

export const CartListItem: FC<CartListItemProps> = ({
    item: { product, quantity, uuid },
    listIndex,
    onItemRemove,
    onItemQuantityChange,
}) => {
    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement>(null);
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const setUpdateTimeout = () => {
        return setTimeout(() => {
            onItemQuantityChange(product.uuid, spinboxRef.current!.valueAsNumber, listIndex, true);
        }, 500);
    };

    return (
        <div
            className="relative flex flex-row flex-wrap items-center gap-4 border-b border-greyLighter py-5 vl:flex-nowrap"
            tid={TIDs.pages_cart_list_item_ + listIndex}
        >
            <div className="flex flex-1 basis-full pr-8 vl:basis-auto vl:pr-0">
                <div className="flex h-12 w-24 shrink-0">
                    <ExtendedNextLink className="relative h-full w-full" href={productSlug} type="product">
                        <Image
                            alt={product.mainImage?.name || product.fullName}
                            className="mx-auto max-h-full w-auto"
                            height={56}
                            src={product.mainImage?.url}
                            width={96}
                        />
                    </ExtendedNextLink>
                </div>

                <div className="flex flex-col items-start gap-4 text-sm font-bold vl:flex-1 vl:flex-row vl:items-center">
                    <div className="h-full text-left vl:w-[16.875rem]" tid={TIDs.pages_cart_list_item_name}>
                        <ExtendedNextLink
                            className="text-sm font-bold uppercase leading-4 text-dark no-underline hover:text-dark hover:no-underline"
                            href={productSlug}
                            type="product"
                        >
                            {product.fullName}
                        </ExtendedNextLink>

                        <div className="text-sm text-greyLight">
                            {t('Code')}: {product.catalogNumber}
                        </div>
                    </div>

                    <div className="block flex-1 vl:text-center">
                        {product.availability.name}

                        {!!product.availableStoresCount && (
                            <span className="ml-1 inline font-normal vl:ml-0 vl:block">
                                {t('or immediately in {{ count }} stores', {
                                    count: product.availableStoresCount,
                                })}
                            </span>
                        )}
                    </div>
                </div>
            </div>

            <div className="flex w-28 items-center vl:w-36">
                <Spinbox
                    defaultValue={quantity}
                    id={uuid}
                    max={product.stockQuantity}
                    min={1}
                    ref={spinboxRef}
                    step={1}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </div>

            <div className="flex items-center justify-end text-sm vl:w-32">
                {formatPrice(product.price.priceWithVat) + '\u00A0/\u00A0' + product.unit.name}
            </div>

            <div
                className="ml-auto flex items-center justify-end text-sm text-primary lg:text-base vl:w-32"
                tid={TIDs.pages_cart_list_item_totalprice}
            >
                {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
            </div>

            <RemoveCartItemButton
                className="absolute right-0 top-5 flex items-center vl:static"
                onItemRemove={onItemRemove}
            />
        </div>
    );
};
