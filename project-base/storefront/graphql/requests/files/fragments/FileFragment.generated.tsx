// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeFileFragment = { __typename: 'File', anchorText: string, url: string, viewUrl: string | null, size: number | null, extension: string | null };

export const FileFragment = gql`
    fragment FileFragment on File {
  __typename
  anchorText
  url
  viewUrl
  size
  extension
}
    `;