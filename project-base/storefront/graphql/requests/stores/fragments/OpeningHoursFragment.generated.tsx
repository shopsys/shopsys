import * as Types from '../../types';

import gql from 'graphql-tag';
export type OpeningHoursFragmentApi = {
    __typename?: 'OpeningHours';
    isOpen: boolean;
    dayOfWeek: number;
    openingHoursOfDays: Array<{
        __typename?: 'OpeningHoursOfDay';
        dayOfWeek: number;
        firstOpeningTime: string | null;
        firstClosingTime: string | null;
        secondOpeningTime: string | null;
        secondClosingTime: string | null;
    }>;
};

export const OpeningHoursFragmentApi = gql`
    fragment OpeningHoursFragment on OpeningHours {
        isOpen
        dayOfWeek
        openingHoursOfDays {
            dayOfWeek
            firstOpeningTime
            firstClosingTime
            secondOpeningTime
            secondClosingTime
        }
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
