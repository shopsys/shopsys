// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportsImageVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeTransportsImage = { __typename?: 'Query', transports: Array<{ __typename?: 'Transport', mainImage: { __typename?: 'Image', name: string | null, url: string } | null }> };


export const TransportsImageDocument = gql`
    query TransportsImage {
  transports {
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