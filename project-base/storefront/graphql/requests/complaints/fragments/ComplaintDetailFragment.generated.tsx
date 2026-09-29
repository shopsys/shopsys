// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { ComplaintResolutionFragment } from './ComplaintResolutionFragment.generated';
import { ComplaintItemFragment } from './ComplaintItemFragment.generated';
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'additionalService'
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

export type TypeComplaintDetailFragment = { uuid: string, number: string, createdAt: string, email: string, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryTelephone: string, deliveryStreet: string, deliveryCity: string, deliveryPostcode: string, bankAccountNumber: string | null, status: string, manualDocumentNumber: string | null, deliveryCountry: { __typename: 'Country', name: string, code: string }, resolution: { name: string, value: string }, items: Array<{ uuid: string, quantity: number, description: string, catnum: string | null, productName: string, orderItem: { __typename: 'OrderItem', uuid: string, unit: string | null, totalPrice: { priceWithVat: string }, relatedItems: Array<{ __typename: 'OrderItem', deliveryDaysExtension: number | null, uuid: string, name: string, catnum: string | null, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> } | null, files: Array<{ __typename: 'File', anchorText: string, url: string, viewUrl: string | null, filesize: number | null, extension: string | null }> | null, product:
      | { fullName: string, slug: string, isVisible: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
      | { fullName: string, slug: string, isVisible: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
      | { fullName: string, slug: string, isVisible: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
     | null }>, order: { __typename: 'Order', uuid: string, number: string, customerUser:
      | { uuid: string }
      | { uuid: string }
      | { uuid: string }
      | { uuid: string }
     | null } | null };

export const ComplaintDetailFragment = gql`
    fragment ComplaintDetailFragment on Complaint {
  uuid
  number
  createdAt
  email
  deliveryFirstName
  deliveryLastName
  deliveryCompanyName
  deliveryTelephone
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    ...CountryFragment
  }
  bankAccountNumber
  resolution {
    ...ComplaintResolutionFragment
  }
  status
  items {
    ...ComplaintItemFragment
  }
  order {
    __typename
    uuid
    number
    customerUser {
      uuid
    }
  }
  manualDocumentNumber
}
    ${CountryFragment}
${ComplaintResolutionFragment}
${ComplaintItemFragment}`;