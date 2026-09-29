import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';

export type ProductInterfaceType =
    | TypeProductDetailFragment
    | TypeMainVariantDetailFragment
    | TypeCartItemFragment['product']
    | TypeListedProductFragment;

export type WatchDogProductType =
    | TypeProductDetailFragment
    | TypeMainVariantDetailFragment
    | TypeMainVariantDetailFragment['variants'][number]
    | TypeListedProductFragment;

export type ProductListViewModeType = 'grid' | 'list';
