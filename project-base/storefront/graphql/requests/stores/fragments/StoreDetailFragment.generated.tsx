// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { OpeningHoursFragment } from './OpeningHoursFragment.generated';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeStoreDetailFragment = (
  { __typename: 'Store' }
  & Pick<Types.TypeStore, 'uuid' | 'slug' | 'description' | 'street' | 'city' | 'postcode' | 'email' | 'phone' | 'directions' | 'specialMessage' | 'latitude' | 'longitude'>
  & { storeName: Types.TypeStore['name'] }
  & { country: (
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  ), openingHours: (
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
  ), breadcrumb: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )>, storeImages: Array<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

export const StoreDetailFragment = gql`
    fragment StoreDetailFragment on Store {
  __typename
  uuid
  slug
  storeName: name
  description
  street
  city
  postcode
  country {
    ...CountryFragment
  }
  openingHours {
    ...OpeningHoursFragment
  }
  email
  phone
  directions
  specialMessage
  latitude
  longitude
  breadcrumb {
    ...BreadcrumbFragment
  }
  storeImages: images {
    ...ImageFragment
  }
}
    ${CountryFragment}
${OpeningHoursFragment}
${BreadcrumbFragment}
${ImageFragment}`;