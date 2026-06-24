// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeFileFragment = { __typename: 'File', anchorText: string, url: string, viewUrl: string | null, filesize: number | null, extension: string | null };

export const FileFragment = gql`
    fragment FileFragment on File {
  __typename
  anchorText
  url
  viewUrl
  filesize
  extension
}
    `;