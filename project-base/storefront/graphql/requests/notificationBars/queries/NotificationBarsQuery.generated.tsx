// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeNotificationBarsVariables = Exact<{ [key: string]: never; }>;


export type TypeNotificationBars = { notificationBars: Array<{ __typename: 'NotificationBar', uuid: string, text: string, rgbColor: string, validityFrom: string | null, validityTo: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> | null };


export const NotificationBarsDocument = gql`
    query NotificationBars @redisCache(ttl: 300) {
  notificationBars {
    __typename
    uuid
    text
    rgbColor
    validityFrom
    validityTo
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;

export function useNotificationBars(options?: Omit<Urql.UseQueryArgs<TypeNotificationBarsVariables>, 'query'>) {
  return Urql.useQuery<TypeNotificationBars, TypeNotificationBarsVariables>({ query: NotificationBarsDocument, ...options });
};