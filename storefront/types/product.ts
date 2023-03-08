import {
    CartItemFragmentApi,
    ListedProductFragmentApi,
    MainVariantDetailFragmentApi,
    ProductDetailFragmentApi,
    SimpleProductFragmentApi,
} from 'graphql/generated';

export type ProductInterfaceType =
    | ProductDetailFragmentApi
    | MainVariantDetailFragmentApi
    | CartItemFragmentApi['product']
    | ListedProductFragmentApi
    | SimpleProductFragmentApi;
