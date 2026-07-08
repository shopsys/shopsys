// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeFileFragment = (
  { __typename: 'File' }
  & Pick<Types.TypeFile, 'anchorText' | 'url' | 'viewUrl' | 'filesize' | 'extension'>
);

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