// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OpeningHoursFragment } from './OpeningHoursFragment.generated';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeListedStoreFragment = (
  { __typename: 'Store' }
  & Pick<Types.TypeStore, 'slug' | 'name' | 'description' | 'latitude' | 'longitude' | 'street' | 'postcode' | 'city' | 'distance' | 'email' | 'phone' | 'specialMessage'>
  & { identifier: Types.TypeStore['uuid'] }
  & { openingHours: (
    { __typename?: 'OpeningHours' }
    & Pick<Types.TypeOpeningHours, 'status' | 'dayOfWeek'>
    & { openingHoursOfDays: Array<(
      { __typename?: 'OpeningHoursOfDay' }
      & Pick<Types.TypeOpeningHoursOfDay, 'date' | 'dayOfWeek'>
      & { openingHoursRanges: Array<(
        { __typename?: 'OpeningHoursRange' }
        & Pick<Types.TypeOpeningHoursRange, 'openingTime' | 'closingTime'>
      )> }
    )> }
  ), country: (
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

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