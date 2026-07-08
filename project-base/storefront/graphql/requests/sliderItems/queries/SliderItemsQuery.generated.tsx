// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SliderItemFragment } from '../fragments/SliderItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSliderItemsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeSliderItemsQuery = (
  { __typename?: 'Query' }
  & { sliderItems: Array<(
    { __typename: 'SliderItem' }
    & Pick<Types.TypeSliderItem, 'uuid' | 'name' | 'link' | 'routeName' | 'description' | 'rgbBackgroundColor' | 'opacity'>
    & { webMainImage: (
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    ), mobileMainImage: (
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    ) }
  )> }
);


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