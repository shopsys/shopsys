// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleCategoryFragment = { __typename: 'Category', uuid: string, name: string, slug: string };

export const SimpleCategoryFragment = gql`
    fragment SimpleCategoryFragment on Category {
  __typename
  uuid
  name
  slug
}
    `;