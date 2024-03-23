import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';

export type ProductInterfaceType =
    | ProductDetailFragment
    | MainVariantDetailFragment
    | CartItemFragment['product']
    | ListedProductFragment
    | SimpleProductFragment;
