import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePromoCodeFragment = { __typename: 'PromoCode', code: string, type: Types.TypePromoCodeTypeEnum };

export const PromoCodeFragment = gql`
    fragment PromoCodeFragment on PromoCode {
  __typename
  code
  type
}
    `;