// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BrandDetailFragment } from '../fragments/BrandDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBrandDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
}>;


export type TypeBrandDetailQuery = (
  { __typename?: 'Query' }
  & { brand: Types.Maybe<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'id' | 'uuid' | 'slug' | 'name' | 'seoH1' | 'seoTitle' | 'seoMetaDescription' | 'description'>
    & { breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
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
    ) }
  )> }
);


export const BrandDetailQueryDocument = gql`
    query BrandDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  brand(urlSlug: $urlSlug) {
    ...BrandDetailFragment
  }
}
    ${BrandDetailFragment}`;

export function useBrandDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeBrandDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandDetailQuery, TypeBrandDetailQueryVariables>({ query: BrandDetailQueryDocument, ...options });
};