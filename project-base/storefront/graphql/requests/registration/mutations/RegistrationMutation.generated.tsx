import * as Types from '../../types';

import gql from 'graphql-tag';
import { TokenFragmentsApi } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type RegistrationMutationVariablesApi = Types.Exact<{
    firstName: Types.Scalars['String']['input'];
    lastName: Types.Scalars['String']['input'];
    email: Types.Scalars['String']['input'];
    password: Types.Scalars['Password']['input'];
    telephone: Types.Scalars['String']['input'];
    street: Types.Scalars['String']['input'];
    city: Types.Scalars['String']['input'];
    postcode: Types.Scalars['String']['input'];
    country: Types.Scalars['String']['input'];
    companyCustomer: Types.Scalars['Boolean']['input'];
    companyName: Types.InputMaybe<Types.Scalars['String']['input']>;
    companyNumber: Types.InputMaybe<Types.Scalars['String']['input']>;
    companyTaxNumber: Types.InputMaybe<Types.Scalars['String']['input']>;
    newsletterSubscription: Types.Scalars['Boolean']['input'];
    previousCartUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
    lastOrderUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;

export type RegistrationMutationApi = {
    __typename?: 'Mutation';
    Register: {
        __typename?: 'LoginResult';
        showCartMergeInfo: boolean;
        tokens: { __typename?: 'Token'; accessToken: string; refreshToken: string };
    };
};

export const RegistrationMutationDocumentApi = gql`
    mutation RegistrationMutation(
        $firstName: String!
        $lastName: String!
        $email: String!
        $password: Password!
        $telephone: String!
        $street: String!
        $city: String!
        $postcode: String!
        $country: String!
        $companyCustomer: Boolean!
        $companyName: String
        $companyNumber: String
        $companyTaxNumber: String
        $newsletterSubscription: Boolean!
        $previousCartUuid: Uuid
        $lastOrderUuid: Uuid
    ) {
        Register(
            input: {
                firstName: $firstName
                lastName: $lastName
                email: $email
                password: $password
                telephone: $telephone
                street: $street
                city: $city
                postcode: $postcode
                country: $country
                companyCustomer: $companyCustomer
                companyName: $companyName
                companyNumber: $companyNumber
                companyTaxNumber: $companyTaxNumber
                newsletterSubscription: $newsletterSubscription
                cartUuid: $previousCartUuid
                lastOrderUuid: $lastOrderUuid
            }
        ) {
            tokens {
                ...TokenFragments
            }
            showCartMergeInfo
        }
    }
    ${TokenFragmentsApi}
`;

export function useRegistrationMutationApi() {
    return Urql.useMutation<RegistrationMutationApi, RegistrationMutationVariablesApi>(RegistrationMutationDocumentApi);
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
