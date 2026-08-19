// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportsImageVariables = Exact<{ [key: string]: never; }>;


export type TypeTransportsImage = { transports: Array<{ name: string, mainImage: { name: string | null, url: string } | null }> };


export const TransportsImageDocument = gql`
    query TransportsImage {
  transports {
    name
    mainImage {
      name
      url
    }
  }
}
    `;

export function useTransportsImage(options?: Omit<Urql.UseQueryArgs<TypeTransportsImageVariables>, 'query'>) {
  return Urql.useQuery<TypeTransportsImage, TypeTransportsImageVariables>({ query: TransportsImageDocument, ...options });
};