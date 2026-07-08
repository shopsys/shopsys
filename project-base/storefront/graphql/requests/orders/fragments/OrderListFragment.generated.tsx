// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedOrderFragment } from './ListedOrderFragment.generated';
export type TypeOrderListFragment = (
  { __typename: 'OrderConnection' }
  & Pick<Types.TypeOrderConnection, 'totalCount'>
  & { pageInfo: (
    { __typename: 'PageInfo' }
    & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
  ), edges: Types.Maybe<Array<Types.Maybe<(
    { __typename: 'OrderEdge' }
    & Pick<Types.TypeOrderEdge, 'cursor'>
    & { node: Types.Maybe<(
      { __typename: 'Order' }
      & Pick<Types.TypeOrder, 'currencyCode' | 'uuid' | 'number' | 'creationDate' | 'isPaid' | 'hasExternalPayment' | 'hasPaymentInProcess' | 'status' | 'note'>
      & { productItems: Array<(
        { __typename: 'OrderItem' }
        & Pick<Types.TypeOrderItem, 'quantity'>
        & { product: Types.Maybe<(
          { __typename: 'MainVariant' }
          & Pick<Types.TypeMainVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        ) | (
          { __typename: 'RegularProduct' }
          & Pick<Types.TypeRegularProduct, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        ) | (
          { __typename: 'Variant' }
          & Pick<Types.TypeVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )> }
        )> }
      )>, totalPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), items: Array<(
        { __typename?: 'OrderItem' }
        & Pick<Types.TypeOrderItem, 'type'>
        & { payment: Types.Maybe<(
          { __typename?: 'Payment' }
          & Pick<Types.TypePayment, 'name'>
          & { mainImage: Types.Maybe<(
            { __typename?: 'Image' }
            & Pick<Types.TypeImage, 'url'>
          )> }
        )>, transport: Types.Maybe<(
          { __typename?: 'Transport' }
          & Pick<Types.TypeTransport, 'name'>
          & { mainImage: Types.Maybe<(
            { __typename?: 'Image' }
            & Pick<Types.TypeImage, 'url'>
          )> }
        )> }
      )> }
    )> }
  )>>> }
);

export const OrderListFragment = gql`
    fragment OrderListFragment on OrderConnection {
  __typename
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedOrderFragment
    }
    cursor
  }
}
    ${PageInfoFragment}
${ListedOrderFragment}`;