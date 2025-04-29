'use client';

import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
};

export const ProductListItemWrapper: FC<ProductItemProps> = ({ children, product, gtmProductListName, listIndex }) => {
    // const { url } = useDomainConfig();
    // const { canSeePrices } = useAuthorization();

    return (
        <ExtendedNextLink
            className="text-text hover:text-link flex grow no-underline select-text hover:no-underline"
            draggable={false}
            href={product.slug}
            type={product.isMainVariant ? 'productMainVariant' : 'product'}
            onMouseUp={() => {
                // onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices);
                // onClick?.(product, listIndex);
            }}
        >
            {children}
        </ExtendedNextLink>
    );
};
