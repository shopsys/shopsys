import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type CleanComparisonMutationVariablesApi = Types.Exact<{
    comparisonUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;

export type CleanComparisonMutationApi = { __typename?: 'Mutation'; cleanComparison: string };

export const CleanComparisonMutationDocumentApi = gql`
    mutation CleanComparisonMutation($comparisonUuid: Uuid) {
        cleanComparison(comparisonUuid: $comparisonUuid)
    }
`;

export function useCleanComparisonMutationApi() {
    return Urql.useMutation<CleanComparisonMutationApi, CleanComparisonMutationVariablesApi>(
        CleanComparisonMutationDocumentApi,
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
