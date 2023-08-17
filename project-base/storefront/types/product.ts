import { CartItemFragmentApi } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { ListedProductFragmentApi } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { MainVariantDetailFragmentApi } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragmentApi } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { SimpleProductFragmentApi } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';

export type ProductInterfaceType =
    | ProductDetailFragmentApi
    | MainVariantDetailFragmentApi
    | CartItemFragmentApi['product']
    | ListedProductFragmentApi
    | SimpleProductFragmentApi;
