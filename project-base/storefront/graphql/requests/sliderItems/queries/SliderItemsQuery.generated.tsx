// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SliderItemFragment } from '../fragments/SliderItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeFriendlyUrlRouteEnum =
  | 'FRONT_ARTICLE_DETAIL'
  | 'FRONT_BLOGARTICLE_DETAIL'
  | 'FRONT_BLOGCATEGORY_DETAIL'
  | 'FRONT_BRAND_DETAIL'
  | 'FRONT_CATEGORY_SEO'
  | 'FRONT_FLAG_DETAIL'
  | 'FRONT_PRODUCT_DETAIL'
  | 'FRONT_PRODUCT_LIST'
  | 'FRONT_STORES_DETAIL';

export type TypeSliderItemsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeSliderItemsQuery = { sliderItems: Array<{ __typename: 'SliderItem', uuid: string, name: string, link: string, routeName: Types.TypeFriendlyUrlRouteEnum | null, description: string | null, rgbBackgroundColor: string, opacity: number, webMainImage: { __typename: 'Image', name: string | null, url: string }, mobileMainImage: { __typename: 'Image', name: string | null, url: string } }> };


export const SliderItemsQueryDocument = gql`
    query SliderItemsQuery @redisCache(ttl: 3600) {
  sliderItems {
    ...SliderItemFragment
  }
}
    ${SliderItemFragment}`;

export function useSliderItemsQuery(options?: Omit<Urql.UseQueryArgs<TypeSliderItemsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSliderItemsQuery, TypeSliderItemsQueryVariables>({ query: SliderItemsQueryDocument, ...options });
};