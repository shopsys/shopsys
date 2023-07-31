import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CartListItemInfo } from './CartListItemInfo';
import { Image } from 'components/Basic/Image/Image';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { CartItemFragmentApi } from 'graphql/generated';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { AddToCartAction } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { MouseEventHandler, useRef } from 'react';

type CartListItemProps = {
    item: CartItemFragmentApi;
    listIndex: number;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
    onItemQuantityChange: AddToCartAction;
};

const TEST_IDENTIFIER = 'pages-cart-list-item-';

export const CartListItem: FC<CartListItemProps> = ({ item, listIndex, onItemRemove, onItemQuantityChange }) => {
    const itemCatnum = item.product.catalogNumber;

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement>(null);
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

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
            onItemQuantityChange(item.product.uuid, spinboxRef.current!.valueAsNumber, listIndex, true);
        }, 500);
    };

    return (
        <div
            className="relative flex flex-row flex-wrap border-b border-greyLighter p-3 lg:py-5 lg:px-0"
            data-testid={TEST_IDENTIFIER + itemCatnum}
        >
            <div className="mb-6 flex w-[93px] pr-4 vl:mb-0" data-testid={TEST_IDENTIFIER + 'image'}>
                <ExtendedNextLink href={item.product.slug} passHref type="product">
                    <a className="relative h-full w-full">
                        <Image
                            image={item.product.mainImage}
                            type="thumbnailExtraSmall"
                            alt={item.product.mainImage?.name || item.product.fullName}
                        />
                    </a>
                </ExtendedNextLink>
            </div>
            <div className="mb-5 flex w-[calc(100%-93px)] flex-col items-start pr-7 text-sm font-bold vl:mb-0 vl:flex-1 vl:flex-row vl:items-center vl:pr-4">
                <CartListItemInfo item={item} />
            </div>
            <div className="flex w-28 items-center vl:w-36 vl:pr-4 " data-testid={TEST_IDENTIFIER + 'spinbox'}>
                <Spinbox
                    min={1}
                    max={item.product.stockQuantity}
                    step={1}
                    defaultValue={item.quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </div>
            <div
                className="vl:pr-15 ml-auto flex items-center vl:ml-0 vl:w-32"
                data-testid={TEST_IDENTIFIER + 'itemprice'}
            >
                <span className="text-sm">
                    {formatPrice(item.product.price.priceWithVat) + '\u00A0/\u00A0' + t('pc')}
                </span>
            </div>
            <div
                className="ml-auto flex w-32 items-center justify-end vl:ml-0 vl:pr-4"
                data-testid={TEST_IDENTIFIER + 'totalprice'}
            >
                <span className="text-sm text-primary lg:text-base">
                    {formatPrice(mapPriceForCalculations(item.product.price.priceWithVat) * item.quantity)}
                </span>
            </div>
            <div className="absolute right-3 top-3 flex items-center lg:right-0 lg:top-4 vl:static">
                <RemoveCartItemButton onItemRemove={onItemRemove} />
            </div>
        </div>
    );
};
