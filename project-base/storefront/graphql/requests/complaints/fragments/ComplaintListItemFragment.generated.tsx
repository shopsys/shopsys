// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintListItemFragment = { uuid: string, number: string, createdAt: string, status: string, resolution: { name: string }, items: Array<{ uuid: string, quantity: number, productName: string, product:
      | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
      | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
      | { slug: string, isVisible: boolean, mainImage: { name: string | null, url: string } | null }
     | null }> };

export const ComplaintListItemFragment = gql`
    fragment ComplaintListItemFragment on Complaint {
  uuid
  number
  createdAt
  status
  resolution {
    name
  }
  items {
    uuid
    quantity
    productName
    product {
      slug
      isVisible
      mainImage {
        name
        url
      }
    }
  }
}
    `;