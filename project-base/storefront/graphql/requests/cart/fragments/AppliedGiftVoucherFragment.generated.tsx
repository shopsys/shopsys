// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeAppliedGiftVoucherFragment = { __typename: 'AppliedGiftVoucher', code: string, valueWithVat: string, valueWithoutVat: string, productName: string | null };

export const AppliedGiftVoucherFragment = gql`
    fragment AppliedGiftVoucherFragment on AppliedGiftVoucher {
  __typename
  code
  valueWithVat
  valueWithoutVat
  productName
}
    `;