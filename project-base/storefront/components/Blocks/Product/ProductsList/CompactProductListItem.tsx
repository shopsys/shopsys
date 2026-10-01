import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { forwardRef } from 'react';
import { ProductItemProps, ProductVisibleItemsConfigType } from './ProductListItem';
import { ProductListItemGridView } from './ProductListItemGridView';

export type CompactProductItemProps = Pick<
    ProductItemProps,
    | 'listIndex'
    | 'gtmProductListName'
    | 'gtmMessageOrigin'
    | 'className'
    | 'size'
    | 'textSize'
    | 'textSizePrice'
    | 'allowKeyboardFocus'
    | 'highlightBadgeText'
> & {
    product: TypeCompactProductFragment;
    visibleItemsConfig?: Pick<
        ProductVisibleItemsConfigType,
        'price' | 'flags' | 'discount' | 'priceFromWord' | 'reviews'
    >;
    onClick?: (product: TypeCompactProductFragment, index: number) => void;
};

export const CompactProductListItem = forwardRef<HTMLLIElement, CompactProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin: _gtmMessageOrigin,
            onClick,
            visibleItemsConfig = {},
            allowKeyboardFocus = true,
            ...props
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { canSeePrices } = useAuthorization();

        return (
            <ProductListItemGridView
                {...props}
                product={product}
                forwardedRef={ref}
                allowKeyboardFocus={allowKeyboardFocus}
                visibleItemsConfig={visibleItemsConfig}
                onProductClick={() => {
                    onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices);
                    onClick?.(product, listIndex);
                }}
            />
        );
    },
);
CompactProductListItem.displayName = 'CompactProductListItem';
