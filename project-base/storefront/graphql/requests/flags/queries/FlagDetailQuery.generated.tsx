// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { FlagDetailFragment } from '../fragments/FlagDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeFlagDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
}>;


export type TypeFlagDetailQuery = (
  { __typename?: 'Query' }
  & { flag: Types.Maybe<(
    { __typename: 'Flag' }
    & Pick<Types.TypeFlag, 'uuid' | 'slug' | 'name'>
    & { breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, products: (
      { __typename: 'ProductConnection' }
      & Pick<Types.TypeProductConnection, 'orderingMode' | 'defaultOrderingMode' | 'totalCount'>
      & { productFilterOptions: (
        { __typename: 'ProductFilterOptions' }
        & Pick<Types.TypeProductFilterOptions, 'minimalPrice' | 'maximalPrice' | 'inStock'>
        & { brands: Types.Maybe<Array<(
          { __typename: 'BrandFilterOption' }
          & Pick<Types.TypeBrandFilterOption, 'count'>
          & { brand: (
            { __typename: 'Brand' }
            & Pick<Types.TypeBrand, 'uuid' | 'name'>
          ) }
        )>>, flags: Types.Maybe<Array<(
          { __typename: 'FlagFilterOption' }
          & Pick<Types.TypeFlagFilterOption, 'count' | 'isSelected'>
          & { flag: (
            { __typename: 'Flag' }
            & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
          ) }
        )>>, parameters: Types.Maybe<Array<(
          { __typename: 'ParameterCheckboxFilterOption' }
          & Pick<Types.TypeParameterCheckboxFilterOption, 'name' | 'uuid' | 'isCollapsed'>
          & { values: Array<(
            { __typename: 'ParameterValueFilterOption' }
            & Pick<Types.TypeParameterValueFilterOption, 'uuid' | 'text' | 'count' | 'isSelected'>
          )> }
        ) | (
          { __typename: 'ParameterColorFilterOption' }
          & Pick<Types.TypeParameterColorFilterOption, 'name' | 'uuid' | 'isCollapsed'>
          & { values: Array<(
            { __typename: 'ParameterValueColorFilterOption' }
            & Pick<Types.TypeParameterValueColorFilterOption, 'uuid' | 'text' | 'count' | 'rgbHex' | 'isSelected'>
            & { colorIcon: Types.Maybe<(
              { __typename?: 'File' }
              & Pick<Types.TypeFile, 'url' | 'anchorText'>
            )> }
          )> }
        ) | (
          { __typename: 'ParameterSliderFilterOption' }
          & Pick<Types.TypeParameterSliderFilterOption, 'name' | 'uuid' | 'minimalValue' | 'maximalValue' | 'isCollapsed' | 'selectedValue' | 'isSelectable'>
          & { unit: Types.Maybe<(
            { __typename: 'Unit' }
            & Pick<Types.TypeUnit, 'name'>
          )> }
        )>> }
      ) }
    ), hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )> }
  )> }
);


export const FlagDetailQueryDocument = gql`
    query FlagDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  flag(urlSlug: $urlSlug) {
    ...FlagDetailFragment
  }
}
    ${FlagDetailFragment}`;

export function useFlagDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeFlagDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeFlagDetailQuery, TypeFlagDetailQueryVariables>({ query: FlagDetailQueryDocument, ...options });
};