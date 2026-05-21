// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLoginWithCredentialMutationVariables = Types.Exact<{
  type: Types.TypeLoginTypeEnum;
  credential: Types.Scalars['String']['input'];
  previousCartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
  productListsUuids: Array<Types.Scalars['Uuid']['input']> | Types.Scalars['Uuid']['input'];
  shouldOverwriteCustomerUserCart?: Types.InputMaybe<Types.Scalars['Boolean']['input']>;
}>;


export type TypeLoginWithCredentialMutation = { __typename?: 'Mutation', LoginWithCredential: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };


export const LoginWithCredentialMutationDocument = gql`
    mutation LoginWithCredentialMutation($type: LoginTypeEnum!, $credential: String!, $previousCartUuid: Uuid, $productListsUuids: [Uuid!]!, $shouldOverwriteCustomerUserCart: Boolean = false) {
  LoginWithCredential(
    input: {type: $type, credential: $credential, cartUuid: $previousCartUuid, productListsUuids: $productListsUuids, shouldOverwriteCustomerUserCart: $shouldOverwriteCustomerUserCart}
  ) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useLoginWithCredentialMutation() {
  return Urql.useMutation<TypeLoginWithCredentialMutation, TypeLoginWithCredentialMutationVariables>(LoginWithCredentialMutationDocument);
};