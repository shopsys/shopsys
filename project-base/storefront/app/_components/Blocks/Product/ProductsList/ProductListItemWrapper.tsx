'use client';

import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useCurrentCustomerData } from 'components/providers/AuthProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { disableClickWhenTextSelected } from 'utils/ui/disableClickWhenTextSelected';

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
};

export const ProductListItemWrapper: FC<ProductItemProps> = ({ children, product, gtmProductListName, listIndex }) => {
    const { url } = useDomainConfig();
    const currentCustomerData = useCurrentCustomerData();

    return (
        <ExtendedNextLink
            className="flex grow select-text text-text no-underline hover:text-link hover:no-underline"
            draggable={false}
            href={product.slug}
            type={product.isMainVariant ? 'productMainVariant' : 'product'}
            onClickExtended={disableClickWhenTextSelected}
            onMouseUp={() => {
                onGtmProductClickEventHandler(
                    product,
                    gtmProductListName,
                    listIndex,
                    url,
                    !!currentCustomerData?.arePricesHidden,
                );
                // onClick?.(product, listIndex);
            }}
        >
            {children}
        </ExtendedNextLink>
    );
};
