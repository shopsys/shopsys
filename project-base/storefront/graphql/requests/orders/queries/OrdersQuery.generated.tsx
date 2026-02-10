// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderListFragment } from '../fragments/OrderListFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrdersQueryVariables = Types.Exact<{
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeOrdersQuery = { __typename?: 'Query', orders: { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', url: string, name: string | null } | null }, payment: { __typename: 'Payment', name: string, type: Types.TypePaymentTypeEnum, mainImage: { __typename?: 'Image', url: string } | null }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null } | null };


export const OrdersQueryDocument = gql`
    query OrdersQuery($after: String, $first: Int) {
  orders(after: $after, first: $first) {
    ...OrderListFragment
  }
}
    ${OrderListFragment}`;

export function useOrdersQuery(options?: Omit<Urql.UseQueryArgs<TypeOrdersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrdersQuery, TypeOrdersQueryVariables>({ query: OrdersQueryDocument, ...options });
};