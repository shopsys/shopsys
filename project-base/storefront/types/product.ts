import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeSimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';

export type ProductInterfaceType =
    | TypeProductDetailFragment
    | TypeMainVariantDetailFragment
    | TypeCartItemFragment['product']
    | TypeListedProductFragment
    | TypeSimpleProductFragment;
