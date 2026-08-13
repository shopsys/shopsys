// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

export type TypeSimpleProductFragment_MainVariant = { __typename: 'MainVariant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, expectedRestockingDate: string | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } };

export type TypeSimpleProductFragment_RegularProduct = { __typename: 'RegularProduct', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, expectedRestockingDate: string | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } };

export type TypeSimpleProductFragment_Variant = { __typename: 'Variant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, expectedRestockingDate: string | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } };

export type TypeSimpleProductFragment =
  | TypeSimpleProductFragment_MainVariant
  | TypeSimpleProductFragment_RegularProduct
  | TypeSimpleProductFragment_Variant
;

export const SimpleProductFragment = gql`
    fragment SimpleProductFragment on Product {
  __typename
  id
  uuid
  catalogNumber
  fullName
  slug
  price {
    ...ProductPriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  unit {
    name
  }
  brand {
    ...SimpleBrandFragment
  }
  categories {
    name
  }
  flags {
    ...SimpleFlagFragment
  }
  expectedRestockingDate
  availability {
    ...AvailabilityFragment
  }
}
    ${ProductPriceFragment}
${ImageFragment}
${SimpleBrandFragment}
${SimpleFlagFragment}
${AvailabilityFragment}`;