import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type PayOrderMutationVariablesApi = Types.Exact<{
    orderUuid: Types.Scalars['Uuid']['input'];
}>;

export type PayOrderMutationApi = {
    __typename?: 'Mutation';
    PayOrder: {
        __typename?: 'PaymentSetupCreationData';
        goPayCreatePaymentSetup: {
            __typename?: 'GoPayCreatePaymentSetup';
            gatewayUrl: string;
            goPayId: string;
            embedJs: string;
        } | null;
    };
};

export const PayOrderMutationDocumentApi = gql`
    mutation PayOrderMutation($orderUuid: Uuid!) {
        PayOrder(orderUuid: $orderUuid) {
            goPayCreatePaymentSetup {
                gatewayUrl
                goPayId
                embedJs
            }
        }
    }
`;

export function usePayOrderMutationApi() {
    return Urql.useMutation<PayOrderMutationApi, PayOrderMutationVariablesApi>(PayOrderMutationDocumentApi);
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
