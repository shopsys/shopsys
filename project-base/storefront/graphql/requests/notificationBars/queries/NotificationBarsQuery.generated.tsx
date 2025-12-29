// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { NotificationBarsFragment } from '../fragments/NotificationBarsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeNotificationBarsVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeNotificationBars = { __typename?: 'Query', notificationBars: Array<{ __typename: 'NotificationBar', text: string, rgbColor: string, validityFrom: any | null, validityTo: any | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> | null };


export const NotificationBarsDocument = gql`
    query NotificationBars @redisCache(ttl: 300) {
  notificationBars {
    ...NotificationBarsFragment
  }
}
    ${NotificationBarsFragment}`;

export function useNotificationBars(options?: Omit<Urql.UseQueryArgs<TypeNotificationBarsVariables>, 'query'>) {
  return Urql.useQuery<TypeNotificationBars, TypeNotificationBarsVariables>({ query: NotificationBarsDocument, ...options });
};