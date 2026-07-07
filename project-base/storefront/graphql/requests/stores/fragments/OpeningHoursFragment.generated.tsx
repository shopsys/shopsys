// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
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

export type TypeOpeningHoursFragment = { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> };

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