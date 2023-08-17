import * as Types from '../../types';

import gql from 'graphql-tag';
import { TokenFragmentsApi } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type LoginVariablesApi = Types.Exact<{
    email: Types.Scalars['String']['input'];
    password: Types.Scalars['Password']['input'];
    previousCartUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;

export type LoginApi = {
    __typename?: 'Mutation';
    Login: {
        __typename?: 'LoginResult';
        showCartMergeInfo: boolean;
        tokens: { __typename?: 'Token'; accessToken: string; refreshToken: string };
    };
};

export const LoginDocumentApi = gql`
    mutation Login($email: String!, $password: Password!, $previousCartUuid: Uuid) {
        Login(input: { email: $email, password: $password, cartUuid: $previousCartUuid }) {
            tokens {
                ...TokenFragments
            }
            showCartMergeInfo
        }
    }
    ${TokenFragmentsApi}
`;

export function useLoginApi() {
    return Urql.useMutation<LoginApi, LoginVariablesApi>(LoginDocumentApi);
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
