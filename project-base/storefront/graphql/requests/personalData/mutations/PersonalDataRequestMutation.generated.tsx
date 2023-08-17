import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type PersonalDataRequestMutationVariablesApi = Types.Exact<{
    email: Types.Scalars['String']['input'];
    type: Types.InputMaybe<Types.PersonalDataAccessRequestTypeEnumApi>;
}>;

export type PersonalDataRequestMutationApi = {
    __typename?: 'Mutation';
    RequestPersonalDataAccess: { __typename?: 'PersonalDataPage'; displaySiteSlug: string; exportSiteSlug: string };
};

export const PersonalDataRequestMutationDocumentApi = gql`
    mutation PersonalDataRequestMutation($email: String!, $type: PersonalDataAccessRequestTypeEnum) {
        RequestPersonalDataAccess(input: { email: $email, type: $type }) {
            displaySiteSlug
            exportSiteSlug
        }
    }
`;

export function usePersonalDataRequestMutationApi() {
    return Urql.useMutation<PersonalDataRequestMutationApi, PersonalDataRequestMutationVariablesApi>(
        PersonalDataRequestMutationDocumentApi,
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
