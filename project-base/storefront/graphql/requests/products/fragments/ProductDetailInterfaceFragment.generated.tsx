import * as Types from '../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragmentApi } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { ProductPriceFragmentApi } from './ProductPriceFragment.generated';
import { ParameterFragmentApi } from '../../parameters/fragments/ParameterFragment.generated';
import { ListedProductFragmentApi } from './ListedProductFragment.generated';
import { SimpleFlagFragmentApi } from '../../flags/fragments/SimpleFlagFragment.generated';
import { AvailabilityFragmentApi } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { SimpleBrandFragmentApi } from '../../brands/fragments/SimpleBrandFragment.generated';
import { VideoTokenFragmentApi } from './VideoTokenFragment.generated';
export type ProductDetailInterfaceFragment_MainVariant_Api = {
    __typename: 'MainVariant';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    namePrefix: string | null;
    nameSuffix: string | null;
    catalogNumber: string;
    ean: string | null;
    description: string | null;
    stockQuantity: number;
    isSellingDenied: boolean;
    seoTitle: string | null;
    seoMetaDescription: string | null;
    isMainVariant: boolean;
    breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
    images: Array<{
        __typename: 'Image';
        name: string | null;
        sizes: Array<{
            __typename: 'ImageSize';
            size: string;
            url: string;
            width: number | null;
            height: number | null;
            additionalSizes: Array<{
                __typename: 'AdditionalSize';
                height: number | null;
                media: string;
                url: string;
                width: number | null;
            }>;
        }>;
    }>;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
    accessories: Array<
        | {
              __typename: 'MainVariant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'RegularProduct';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'Variant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
    >;
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    productVideos: Array<{ __typename: 'VideoToken'; description: string; token: string }>;
};

export type ProductDetailInterfaceFragment_RegularProduct_Api = {
    __typename: 'RegularProduct';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    namePrefix: string | null;
    nameSuffix: string | null;
    catalogNumber: string;
    ean: string | null;
    description: string | null;
    stockQuantity: number;
    isSellingDenied: boolean;
    seoTitle: string | null;
    seoMetaDescription: string | null;
    isMainVariant: boolean;
    breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
    images: Array<{
        __typename: 'Image';
        name: string | null;
        sizes: Array<{
            __typename: 'ImageSize';
            size: string;
            url: string;
            width: number | null;
            height: number | null;
            additionalSizes: Array<{
                __typename: 'AdditionalSize';
                height: number | null;
                media: string;
                url: string;
                width: number | null;
            }>;
        }>;
    }>;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
    accessories: Array<
        | {
              __typename: 'MainVariant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'RegularProduct';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'Variant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
    >;
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    productVideos: Array<{ __typename: 'VideoToken'; description: string; token: string }>;
};

export type ProductDetailInterfaceFragment_Variant_Api = {
    __typename: 'Variant';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    namePrefix: string | null;
    nameSuffix: string | null;
    catalogNumber: string;
    ean: string | null;
    description: string | null;
    stockQuantity: number;
    isSellingDenied: boolean;
    seoTitle: string | null;
    seoMetaDescription: string | null;
    isMainVariant: boolean;
    breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
    images: Array<{
        __typename: 'Image';
        name: string | null;
        sizes: Array<{
            __typename: 'ImageSize';
            size: string;
            url: string;
            width: number | null;
            height: number | null;
            additionalSizes: Array<{
                __typename: 'AdditionalSize';
                height: number | null;
                media: string;
                url: string;
                width: number | null;
            }>;
        }>;
    }>;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
    accessories: Array<
        | {
              __typename: 'MainVariant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'RegularProduct';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
        | {
              __typename: 'Variant';
              id: number;
              uuid: string;
              slug: string;
              fullName: string;
              name: string;
              stockQuantity: number;
              isSellingDenied: boolean;
              availableStoresCount: number;
              exposedStoresCount: number;
              catalogNumber: string;
              isMainVariant: boolean;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
              price: {
                  __typename: 'ProductPrice';
                  priceWithVat: string;
                  priceWithoutVat: string;
                  vatAmount: string;
                  isPriceFrom: boolean;
              };
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename: 'Category'; name: string }>;
          }
    >;
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    productVideos: Array<{ __typename: 'VideoToken'; description: string; token: string }>;
};

export type ProductDetailInterfaceFragmentApi =
    | ProductDetailInterfaceFragment_MainVariant_Api
    | ProductDetailInterfaceFragment_RegularProduct_Api
    | ProductDetailInterfaceFragment_Variant_Api;

export const ProductDetailInterfaceFragmentApi = gql`
    fragment ProductDetailInterfaceFragment on Product {
        __typename
        id
        uuid
        slug
        fullName
        name
        namePrefix
        nameSuffix
        breadcrumb {
            ...BreadcrumbFragment
        }
        catalogNumber
        ean
        description
        images {
            ...ImageSizesFragment
        }
        price {
            ...ProductPriceFragment
        }
        parameters {
            ...ParameterFragment
        }
        stockQuantity
        accessories {
            ...ListedProductFragment
        }
        brand {
            ...SimpleBrandFragment
        }
        categories {
            name
        }
        flags {
            ...SimpleFlagFragment
        }
        isSellingDenied
        availability {
            ...AvailabilityFragment
        }
        seoTitle
        seoMetaDescription
        isMainVariant
        productVideos {
            ...VideoTokenFragment
        }
    }
    ${BreadcrumbFragmentApi}
    ${ImageSizesFragmentApi}
    ${ProductPriceFragmentApi}
    ${ParameterFragmentApi}
    ${ListedProductFragmentApi}
    ${SimpleBrandFragmentApi}
    ${SimpleFlagFragmentApi}
    ${AvailabilityFragmentApi}
    ${VideoTokenFragmentApi}
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
