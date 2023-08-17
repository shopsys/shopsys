import * as Types from '../../types';

import gql from 'graphql-tag';
import { TokenFragmentsApi } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type RecoverPasswordMutationVariablesApi = Types.Exact<{
    email: Types.Scalars['String']['input'];
    hash: Types.Scalars['String']['input'];
    newPassword: Types.Scalars['Password']['input'];
}>;

export type RecoverPasswordMutationApi = {
    __typename?: 'Mutation';
    RecoverPassword: {
        __typename?: 'LoginResult';
        tokens: { __typename?: 'Token'; accessToken: string; refreshToken: string };
    };
};

export const RecoverPasswordMutationDocumentApi = gql`
    mutation RecoverPasswordMutation($email: String!, $hash: String!, $newPassword: Password!) {
        RecoverPassword(input: { email: $email, hash: $hash, newPassword: $newPassword }) {
            tokens {
                ...TokenFragments
            }
        }
    }
    ${TokenFragmentsApi}
`;

export function useRecoverPasswordMutationApi() {
    return Urql.useMutation<RecoverPasswordMutationApi, RecoverPasswordMutationVariablesApi>(
        RecoverPasswordMutationDocumentApi,
    );
}

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
