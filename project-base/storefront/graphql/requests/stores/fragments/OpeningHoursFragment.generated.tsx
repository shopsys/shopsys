// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOpeningHoursFragment = { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> };

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