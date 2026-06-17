// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OpeningHoursFragment } from './OpeningHoursFragment.generated';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
/** Status of store opening */
export type TypeStoreOpeningStatusEnum =
  /** Store is currently closed */
  | 'CLOSED'
  /** Store will be closed soon */
  | 'CLOSED_SOON'
  /** Store is currently opened */
  | 'OPEN'
  /** Store will be opened soon */
  | 'OPEN_SOON';

export type TypeListedStoreFragment = { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const ListedStoreFragment = gql`
    fragment ListedStoreFragment on Store {
  __typename
  slug
  identifier: uuid
  name
  description
  openingHours {
    ...OpeningHoursFragment
  }
  latitude
  longitude
  street
  postcode
  city
  distance
  email
  phone
  specialMessage
  country {
    ...CountryFragment
  }
  mainImage {
    ...ImageFragment
  }
}
    ${OpeningHoursFragment}
${CountryFragment}
${ImageFragment}`;