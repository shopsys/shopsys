import * as Types from '../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragmentApi } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SetDefaultDeliveryAddressMutationVariablesApi = Types.Exact<{
    deliveryAddressUuid: Types.Scalars['Uuid']['input'];
}>;

export type SetDefaultDeliveryAddressMutationApi = {
    __typename?: 'Mutation';
    SetDefaultDeliveryAddress:
        | {
              __typename?: 'CompanyCustomerUser';
              uuid: string;
              defaultDeliveryAddress: {
                  __typename: 'DeliveryAddress';
                  uuid: string;
                  companyName: string | null;
                  street: string | null;
                  city: string | null;
                  postcode: string | null;
                  telephone: string | null;
                  firstName: string | null;
                  lastName: string | null;
                  country: { __typename: 'Country'; name: string; code: string } | null;
              } | null;
          }
        | {
              __typename?: 'RegularCustomerUser';
              uuid: string;
              defaultDeliveryAddress: {
                  __typename: 'DeliveryAddress';
                  uuid: string;
                  companyName: string | null;
                  street: string | null;
                  city: string | null;
                  postcode: string | null;
                  telephone: string | null;
                  firstName: string | null;
                  lastName: string | null;
                  country: { __typename: 'Country'; name: string; code: string } | null;
              } | null;
          };
};

export const SetDefaultDeliveryAddressMutationDocumentApi = gql`
    mutation SetDefaultDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
        SetDefaultDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
            uuid
            defaultDeliveryAddress {
                ...DeliveryAddressFragment
            }
        }
    }
    ${DeliveryAddressFragmentApi}
`;

export function useSetDefaultDeliveryAddressMutationApi() {
    return Urql.useMutation<SetDefaultDeliveryAddressMutationApi, SetDefaultDeliveryAddressMutationVariablesApi>(
        SetDefaultDeliveryAddressMutationDocumentApi,
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
