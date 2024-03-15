import {
    CartItemFragmentApi,
    ListedProductFragmentApi,
    ProductDetailFragmentApi,
    SimpleProductFragmentApi,
} from 'graphql/generated';

export type ProductInterfaceType =
    | ProductDetailFragmentApi
    | CartItemFragmentApi['product']
    | ListedProductFragmentApi
    | SimpleProductFragmentApi;
