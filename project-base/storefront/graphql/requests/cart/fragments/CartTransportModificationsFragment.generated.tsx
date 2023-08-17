import * as Types from '../../types';

import gql from 'graphql-tag';
export type CartTransportModificationsFragmentApi = {
    __typename: 'CartTransportModificationsResult';
    transportPriceChanged: boolean;
    transportUnavailable: boolean;
    transportWeightLimitExceeded: boolean;
    personalPickupStoreUnavailable: boolean;
};

export const CartTransportModificationsFragmentApi = gql`
    fragment CartTransportModificationsFragment on CartTransportModificationsResult {
        __typename
        transportPriceChanged
        transportUnavailable
        transportWeightLimitExceeded
        personalPickupStoreUnavailable
    }
`;

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
