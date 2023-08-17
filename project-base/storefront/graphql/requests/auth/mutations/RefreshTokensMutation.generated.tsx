import * as Types from '../../types';

import gql from 'graphql-tag';
import { TokenFragmentsApi } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type RefreshTokensVariablesApi = Types.Exact<{
    refreshToken: Types.Scalars['String']['input'];
}>;

export type RefreshTokensApi = {
    __typename?: 'Mutation';
    RefreshTokens: { __typename?: 'Token'; accessToken: string; refreshToken: string };
};

export const RefreshTokensDocumentApi = gql`
    mutation RefreshTokens($refreshToken: String!) {
        RefreshTokens(input: { refreshToken: $refreshToken }) {
            ...TokenFragments
        }
    }
    ${TokenFragmentsApi}
`;

export function useRefreshTokensApi() {
    return Urql.useMutation<RefreshTokensApi, RefreshTokensVariablesApi>(RefreshTokensDocumentApi);
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
