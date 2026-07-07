// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintListItemFragment = { __typename?: 'Complaint', uuid: string, number: string, createdAt: any, status: string, resolution: { __typename?: 'ComplaintResolution', name: string }, items: Array<{ __typename?: 'ComplaintItem', uuid: string, quantity: number, productName: string, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename?: 'Image', name: string | null, url: string } | null } | null }> };

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