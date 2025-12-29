// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreFragment } from '../fragments/ListedStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeStoreQueryVariables = Types.Exact<{
  uuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type TypeStoreQuery = { __typename?: 'Query', store: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };


export const StoreQueryDocument = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragment}`;

export function useStoreQuery(options?: Omit<Urql.UseQueryArgs<TypeStoreQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoreQuery, TypeStoreQueryVariables>({ query: StoreQueryDocument, ...options });
};