// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOpeningHoursFragment = (
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
);

export const OpeningHoursFragment = gql`
    fragment OpeningHoursFragment on OpeningHours {
  status
  dayOfWeek
  openingHoursOfDays {
    date
    dayOfWeek
    openingHoursRanges {
      openingTime
      closingTime
    }
  }
}
    `;