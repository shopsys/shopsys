import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type IsCustomerUserRegisteredQueryVariablesApi = Types.Exact<{
    email: Types.Scalars['String']['input'];
}>;

export type IsCustomerUserRegisteredQueryApi = { __typename?: 'Query'; isCustomerUserRegistered: boolean };

export const IsCustomerUserRegisteredQueryDocumentApi = gql`
    query IsCustomerUserRegisteredQuery($email: String!) {
        isCustomerUserRegistered(email: $email)
    }
`;

export function useIsCustomerUserRegisteredQueryApi(
    options: Omit<Urql.UseQueryArgs<IsCustomerUserRegisteredQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<IsCustomerUserRegisteredQueryApi, IsCustomerUserRegisteredQueryVariablesApi>({
        query: IsCustomerUserRegisteredQueryDocumentApi,
        ...options,
    });
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
