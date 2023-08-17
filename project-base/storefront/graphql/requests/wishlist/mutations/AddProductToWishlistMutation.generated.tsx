import * as Types from '../../types';

import gql from 'graphql-tag';
import { WishlistFragmentApi } from '../fragments/WishlistFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type AddProductToWishlistMutationVariablesApi = Types.Exact<{
    productUuid: Types.Scalars['Uuid']['input'];
    wishlistUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;

export type AddProductToWishlistMutationApi = {
    __typename?: 'Mutation';
    addProductToWishlist: {
        __typename: 'Wishlist';
        uuid: string;
        products: Array<
            | {
                  __typename: 'MainVariant';
                  id: number;
                  uuid: string;
                  slug: string;
                  fullName: string;
                  name: string;
                  stockQuantity: number;
                  isSellingDenied: boolean;
                  availableStoresCount: number;
                  exposedStoresCount: number;
                  catalogNumber: string;
                  isMainVariant: boolean;
                  flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                  mainImage: {
                      __typename: 'Image';
                      name: string | null;
                      sizes: Array<{
                          __typename: 'ImageSize';
                          size: string;
                          url: string;
                          width: number | null;
                          height: number | null;
                          additionalSizes: Array<{
                              __typename: 'AdditionalSize';
                              height: number | null;
                              media: string;
                              url: string;
                              width: number | null;
                          }>;
                      }>;
                  } | null;
                  price: {
                      __typename: 'ProductPrice';
                      priceWithVat: string;
                      priceWithoutVat: string;
                      vatAmount: string;
                      isPriceFrom: boolean;
                  };
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
                  brand: { __typename: 'Brand'; name: string; slug: string } | null;
                  categories: Array<{ __typename: 'Category'; name: string }>;
              }
            | {
                  __typename: 'RegularProduct';
                  id: number;
                  uuid: string;
                  slug: string;
                  fullName: string;
                  name: string;
                  stockQuantity: number;
                  isSellingDenied: boolean;
                  availableStoresCount: number;
                  exposedStoresCount: number;
                  catalogNumber: string;
                  isMainVariant: boolean;
                  flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                  mainImage: {
                      __typename: 'Image';
                      name: string | null;
                      sizes: Array<{
                          __typename: 'ImageSize';
                          size: string;
                          url: string;
                          width: number | null;
                          height: number | null;
                          additionalSizes: Array<{
                              __typename: 'AdditionalSize';
                              height: number | null;
                              media: string;
                              url: string;
                              width: number | null;
                          }>;
                      }>;
                  } | null;
                  price: {
                      __typename: 'ProductPrice';
                      priceWithVat: string;
                      priceWithoutVat: string;
                      vatAmount: string;
                      isPriceFrom: boolean;
                  };
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
                  brand: { __typename: 'Brand'; name: string; slug: string } | null;
                  categories: Array<{ __typename: 'Category'; name: string }>;
              }
            | {
                  __typename: 'Variant';
                  id: number;
                  uuid: string;
                  slug: string;
                  fullName: string;
                  name: string;
                  stockQuantity: number;
                  isSellingDenied: boolean;
                  availableStoresCount: number;
                  exposedStoresCount: number;
                  catalogNumber: string;
                  isMainVariant: boolean;
                  flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                  mainImage: {
                      __typename: 'Image';
                      name: string | null;
                      sizes: Array<{
                          __typename: 'ImageSize';
                          size: string;
                          url: string;
                          width: number | null;
                          height: number | null;
                          additionalSizes: Array<{
                              __typename: 'AdditionalSize';
                              height: number | null;
                              media: string;
                              url: string;
                              width: number | null;
                          }>;
                      }>;
                  } | null;
                  price: {
                      __typename: 'ProductPrice';
                      priceWithVat: string;
                      priceWithoutVat: string;
                      vatAmount: string;
                      isPriceFrom: boolean;
                  };
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
                  brand: { __typename: 'Brand'; name: string; slug: string } | null;
                  categories: Array<{ __typename: 'Category'; name: string }>;
              }
        >;
    };
};

export const AddProductToWishlistMutationDocumentApi = gql`
    mutation AddProductToWishlistMutation($productUuid: Uuid!, $wishlistUuid: Uuid) {
        addProductToWishlist(productUuid: $productUuid, wishlistUuid: $wishlistUuid) {
            ...WishlistFragment
        }
    }
    ${WishlistFragmentApi}
`;

export function useAddProductToWishlistMutationApi() {
    return Urql.useMutation<AddProductToWishlistMutationApi, AddProductToWishlistMutationVariablesApi>(
        AddProductToWishlistMutationDocumentApi,
    );
}

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
