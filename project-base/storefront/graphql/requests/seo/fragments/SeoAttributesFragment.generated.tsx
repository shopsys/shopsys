// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSeoAttributesFragment = { __typename: 'SeoAttributes', title: string | null, metaDescription: string | null, h1: string | null, metaRobots: string | null, canonicalUrl: string | null };

export const SeoAttributesFragment = gql`
    fragment SeoAttributesFragment on SeoAttributes {
  __typename
  title
  metaDescription
  h1
  metaRobots
  canonicalUrl
}
    `;