// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { CategoryPreviewFragment } from './CategoryPreviewFragment.generated';
import { ListedProductConnectionPreviewFragment } from '../../products/fragments/ListedProductConnectionPreviewFragment.generated';
import { CategoryBestsellerFragment } from './CategoryBestsellerFragment.generated';
export type TypeCategoryDetailFragment = (
  { __typename: 'Category' }
  & Pick<Types.TypeCategory, 'id' | 'uuid' | 'slug' | 'originalCategorySlug' | 'zboziCategory' | 'name' | 'description' | 'seoH1' | 'seoTitle' | 'seoMetaDescription' | 'automatedFilters'>
  & { images: Array<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, readyCategorySeoMixLinks: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )>, hreflangLinks: Array<(
    { __typename?: 'HreflangLink' }
    & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
  )>, breadcrumb: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )>, categoryHierarchy: Array<(
    { __typename?: 'CategoryHierarchyItem' }
    & Pick<Types.TypeCategoryHierarchyItem, 'id' | 'name'>
  )>, children: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, products: (
      { __typename: 'ProductConnection' }
      & Pick<Types.TypeProductConnection, 'totalCount'>
    ) }
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
  ), bestsellers: Array<(
    { __typename: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'variantsCount' | 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { unit: (
      { __typename: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'url'>
    )>, price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat'>
      ) }
    ), availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name'>
    )>, categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )> }
  ) | (
    { __typename: 'RegularProduct' }
    & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { unit: (
      { __typename: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'url'>
    )>, price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat'>
      ) }
    ), availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name'>
    )>, categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )> }
  ) | (
    { __typename: 'Variant' }
    & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
    & { mainVariant: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'slug'>
    )>, unit: (
      { __typename: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'url'>
    )>, price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat'>
      ) }
    ), availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name'>
    )>, categories: Array<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )> }
  )> }
);

export const CategoryDetailFragment = gql`
    fragment CategoryDetailFragment on Category {
  __typename
  id
  uuid
  slug
  originalCategorySlug
  zboziCategory
  name
  description
  images {
    ...ImageFragment
  }
  seoH1
  seoTitle
  seoMetaDescription
  readyCategorySeoMixLinks {
    __typename
    name
    slug
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  categoryHierarchy {
    id
    name
  }
  children {
    ...CategoryPreviewFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
  bestsellers {
    ...CategoryBestsellerFragment
  }
  automatedFilters
}
    ${ImageFragment}
${HreflangLinksFragment}
${BreadcrumbFragment}
${CategoryPreviewFragment}
${ListedProductConnectionPreviewFragment}
${CategoryBestsellerFragment}`;