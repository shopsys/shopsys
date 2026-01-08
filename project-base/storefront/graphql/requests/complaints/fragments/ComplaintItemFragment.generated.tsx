// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from '../../orders/fragments/OrderDetailItemFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { FileFragment } from '../../files/fragments/FileFragment.generated';
export type TypeComplaintItemFragment = { __typename?: 'ComplaintItem', quantity: number, description: string, catnum: string | null, productName: string, orderItem: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, customerUser: { __typename?: 'CompanyCustomerUser', uuid: string } | { __typename?: 'CurrentCompanyCustomerUser', uuid: string } | { __typename?: 'CurrentRegularCustomerUser', uuid: string } | { __typename?: 'RegularCustomerUser', uuid: string } | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product: { __typename?: 'MainVariant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'RegularProduct', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'Variant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | null } | null, files: Array<{ __typename: 'File', anchorText: string, url: string }> | null, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };

export const ComplaintItemFragment = gql`
    fragment ComplaintItemFragment on ComplaintItem {
  quantity
  description
  orderItem {
    ...OrderDetailItemFragment
  }
  files {
    ...FileFragment
  }
  product {
    mainImage {
      ...ImageFragment
    }
    slug
    isVisible
  }
  catnum
  productName
}
    ${OrderDetailItemFragment}
${FileFragment}
${ImageFragment}`;