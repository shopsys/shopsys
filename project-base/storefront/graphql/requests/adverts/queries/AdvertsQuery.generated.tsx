import * as Types from '../../types';

import gql from 'graphql-tag';
import { AdvertsFragmentApi } from '../fragments/AdvertsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type AdvertsQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type AdvertsQueryApi = {
    __typename?: 'Query';
    adverts: Array<
        | {
              __typename: 'AdvertCode';
              code: string;
              uuid: string;
              name: string;
              positionName: string;
              type: string;
              categories: Array<{ __typename: 'Category'; uuid: string; name: string; slug: string }>;
          }
        | {
              __typename: 'AdvertImage';
              link: string | null;
              uuid: string;
              name: string;
              positionName: string;
              type: string;
              mainImage: {
                  __typename: 'Image';
                  position: number | null;
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
              mainImageMobile: {
                  __typename: 'Image';
                  position: number | null;
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
              categories: Array<{ __typename: 'Category'; uuid: string; name: string; slug: string }>;
          }
    >;
};

export const AdvertsQueryDocumentApi = gql`
    query AdvertsQuery {
        adverts {
            ...AdvertsFragment
        }
    }
    ${AdvertsFragmentApi}
`;

export function useAdvertsQueryApi(options?: Omit<Urql.UseQueryArgs<AdvertsQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<AdvertsQueryApi, AdvertsQueryVariablesApi>({ query: AdvertsQueryDocumentApi, ...options });
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
