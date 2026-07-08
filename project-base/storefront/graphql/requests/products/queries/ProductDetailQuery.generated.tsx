// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductDetailFragment } from '../fragments/ProductDetailFragment.generated';
import { MainVariantDetailFragment } from '../fragments/MainVariantDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeProductDetailQuery = (
  { __typename?: 'Query' }
  & { product: Types.Maybe<(
    { __typename: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'name' | 'namePrefix' | 'nameSuffix' | 'catalogNumber' | 'ean' | 'description' | 'zboziCategory' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'stockQuantity' | 'isAllowedNegativeStock' | 'seoTitle' | 'seoMetaDescription' | 'isMainVariant' | 'isInquiryType'>
    & { variants: Array<(
      { __typename: 'Variant' }
      & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'stockQuantity' | 'isAllowedNegativeStock' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'availableStoresCount' | 'catalogNumber' | 'isMainVariant' | 'isInquiryType'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'url' | 'name'>
      )>, storeAvailabilities: Array<(
        { __typename?: 'StoreAvailability' }
        & Pick<Types.TypeStoreAvailability, 'availabilityInformation' | 'availabilityStatus'>
        & { store: Types.Maybe<(
          { __typename?: 'Store' }
          & Pick<Types.TypeStore, 'slug'>
          & { storeName: Types.TypeStore['name'] }
        )> }
      )>, unit: (
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      ), flags: Array<(
        { __typename: 'Flag' }
        & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
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
    )>, breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, unit: (
      { __typename?: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), images: Array<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), parameters: Array<(
      { __typename: 'Parameter' }
      & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
      & { unit: Types.Maybe<(
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      )>, values: Array<(
        { __typename: 'ParameterValue' }
        & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
        & { colorIcon: Types.Maybe<(
          { __typename?: 'File' }
          & Pick<Types.TypeFile, 'url' | 'anchorText'>
        )> }
      )> }
    )>, accessories: Array<(
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
    )>, brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )>, categories: Array<(
      { __typename?: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )>, flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )>, productVideos: Array<(
      { __typename: 'VideoToken' }
      & Pick<Types.TypeVideoToken, 'description' | 'token'>
    )>, relatedProducts: Array<(
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
    )>, files: Array<(
      { __typename: 'File' }
      & Pick<Types.TypeFile, 'anchorText' | 'url' | 'viewUrl' | 'filesize' | 'extension'>
    )>, gifts: Array<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    ) | (
      { __typename?: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    ) | (
      { __typename?: 'Variant' }
      & Pick<Types.TypeVariant, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    )> }
  ) | (
    { __typename: 'RegularProduct' }
    & Pick<Types.TypeRegularProduct, 'shortDescription' | 'usps' | 'availableStoresCount' | 'id' | 'uuid' | 'slug' | 'fullName' | 'name' | 'namePrefix' | 'nameSuffix' | 'catalogNumber' | 'ean' | 'description' | 'zboziCategory' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'isSellingDenied' | 'isCurrentlyOutOfStock' | 'stockQuantity' | 'isAllowedNegativeStock' | 'seoTitle' | 'seoMetaDescription' | 'isMainVariant' | 'isInquiryType'>
    & { storeAvailabilities: Array<(
      { __typename?: 'StoreAvailability' }
      & Pick<Types.TypeStoreAvailability, 'availabilityInformation' | 'availabilityStatus'>
      & { store: Types.Maybe<(
        { __typename?: 'Store' }
        & Pick<Types.TypeStore, 'slug'>
        & { storeName: Types.TypeStore['name'] }
      )> }
    )>, breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, unit: (
      { __typename?: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    ), images: Array<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, price: (
      { __typename: 'ProductPrice' }
      & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
      & { basicPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
      ) }
    ), parameters: Array<(
      { __typename: 'Parameter' }
      & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
      & { unit: Types.Maybe<(
        { __typename: 'Unit' }
        & Pick<Types.TypeUnit, 'name'>
      )>, values: Array<(
        { __typename: 'ParameterValue' }
        & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
        & { colorIcon: Types.Maybe<(
          { __typename?: 'File' }
          & Pick<Types.TypeFile, 'url' | 'anchorText'>
        )> }
      )> }
    )>, accessories: Array<(
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
    )>, brand: Types.Maybe<(
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'name' | 'slug'>
    )>, categories: Array<(
      { __typename?: 'Category' }
      & Pick<Types.TypeCategory, 'name'>
    )>, flags: Array<(
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    )>, availability: (
      { __typename: 'Availability' }
      & Pick<Types.TypeAvailability, 'name' | 'status'>
    ), hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )>, productVideos: Array<(
      { __typename: 'VideoToken' }
      & Pick<Types.TypeVideoToken, 'description' | 'token'>
    )>, relatedProducts: Array<(
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
    )>, files: Array<(
      { __typename: 'File' }
      & Pick<Types.TypeFile, 'anchorText' | 'url' | 'viewUrl' | 'filesize' | 'extension'>
    )>, gifts: Array<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    ) | (
      { __typename?: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    ) | (
      { __typename?: 'Variant' }
      & Pick<Types.TypeVariant, 'uuid' | 'name'>
      & { images: Array<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ) }
    )> }
  ) | (
    { __typename: 'Variant' }
    & Pick<Types.TypeVariant, 'catalogNumber'>
    & { mainVariant: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'slug'>
    )> }
  )> }
);


export const ProductDetailQueryDocument = gql`
    query ProductDetailQuery($urlSlug: String) @friendlyUrl {
  product(urlSlug: $urlSlug) {
    ... on Product {
      ...ProductDetailFragment
    }
    ... on MainVariant {
      ...MainVariantDetailFragment
    }
    ... on Variant {
      __typename
      catalogNumber
      mainVariant {
        slug
      }
    }
  }
}
    ${ProductDetailFragment}
${MainVariantDetailFragment}`;

export function useProductDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeProductDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductDetailQuery, TypeProductDetailQueryVariables>({ query: ProductDetailQueryDocument, ...options });
};