// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ProductPriceFragment } from '../../products/fragments/ProductPriceFragment.generated';
export type TypeOrderDetailItemFragment = { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, customerUser: { __typename?: 'CompanyCustomerUser', uuid: string } | { __typename?: 'CurrentCompanyCustomerUser', uuid: string } | { __typename?: 'CurrentRegularCustomerUser', uuid: string } | { __typename?: 'RegularCustomerUser', uuid: string } | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product: { __typename?: 'MainVariant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'RegularProduct', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'Variant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | null, transport: { __typename?: 'Transport', isPersonalPickup: boolean, transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { __typename?: 'Image', url: string } | null } | null, payment: { __typename?: 'Payment', mainImage: { __typename?: 'Image', url: string } | null } | null };

export const OrderDetailItemFragment = gql`
    fragment OrderDetailItemFragment on OrderItem {
  __typename
  uuid
  name
  unitPrice {
    ...PriceFragment
  }
  totalPrice {
    ...PriceFragment
  }
  vatRate
  quantity
  unit
  type
  order {
    uuid
    number
    creationDate
    customerUser {
      uuid
    }
    withdrawalRequest {
      __typename
    }
  }
  product {
    catalogNumber
    slug
    isVisible
    isSellingDenied
    isInquiryType
    isCurrentlyOutOfStock
    promotionBuyQuantity
    promotionFreeQuantity
    categories {
      name
    }
    mainImage {
      ...ImageFragment
    }
    price {
      ...ProductPriceFragment
    }
    giftPrice {
      ...ProductPriceFragment
    }
    availability {
      name
      status
    }
  }
  transport {
    isPersonalPickup
    transportTypeCode
    mainImage {
      url
    }
  }
  payment {
    mainImage {
      url
    }
  }
}
    ${PriceFragment}
${ImageFragment}
${ProductPriceFragment}`;