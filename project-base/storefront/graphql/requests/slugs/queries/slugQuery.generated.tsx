import * as Types from '../../types';

import gql from 'graphql-tag';
import { ProductDetailFragmentApi } from '../../products/fragments/ProductDetailFragment.generated';
import { StoreDetailFragmentApi } from '../../stores/fragments/StoreDetailFragment.generated';
import { MainVariantDetailFragmentApi } from '../../products/fragments/MainVariantDetailFragment.generated';
import { CategoryDetailFragmentApi } from '../../categories/fragments/CategoryDetailFragment.generated';
import { ArticleDetailFragmentApi } from '../../articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { BlogArticleDetailFragmentApi } from '../../articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { BrandDetailFragmentApi } from '../../brands/fragments/BrandDetailFragment.generated';
import { FlagDetailFragmentApi } from '../../flags/fragments/FlagDetailFragment.generated';
import { BlogCategoryDetailFragmentApi } from '../../blogCategories/fragments/BlogCategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SlugQueryVariablesApi = Types.Exact<{
    slug: Types.Scalars['String']['input'];
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
}>;

export type SlugQueryApi = {
    __typename?: 'Query';
    slug:
        | {
              __typename: 'ArticleSite';
              uuid: string;
              slug: string;
              placement: string;
              text: string | null;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              createdAt: any;
              articleName: string;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
          }
        | {
              __typename: 'BlogArticle';
              id: number;
              uuid: string;
              name: string;
              slug: string;
              link: string;
              text: string | null;
              publishDate: any;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              seoH1: string | null;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
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
          }
        | {
              __typename: 'BlogCategory';
              uuid: string;
              name: string;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              articlesTotalCount: number;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
              blogCategoriesTree: Array<{
                  __typename: 'BlogCategory';
                  uuid: string;
                  name: string;
                  link: string;
                  children: Array<{
                      __typename: 'BlogCategory';
                      uuid: string;
                      name: string;
                      link: string;
                      children: Array<{
                          __typename: 'BlogCategory';
                          uuid: string;
                          name: string;
                          link: string;
                          children: Array<{
                              __typename: 'BlogCategory';
                              uuid: string;
                              name: string;
                              link: string;
                              children: Array<{
                                  __typename: 'BlogCategory';
                                  uuid: string;
                                  name: string;
                                  link: string;
                                  parent: { __typename?: 'BlogCategory'; name: string } | null;
                              }>;
                              parent: { __typename?: 'BlogCategory'; name: string } | null;
                          }>;
                          parent: { __typename?: 'BlogCategory'; name: string } | null;
                      }>;
                      parent: { __typename?: 'BlogCategory'; name: string } | null;
                  }>;
                  parent: { __typename?: 'BlogCategory'; name: string } | null;
              }>;
          }
        | {
              __typename: 'Brand';
              id: number;
              uuid: string;
              slug: string;
              name: string;
              seoH1: string | null;
              description: string | null;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
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
              products: {
                  __typename: 'ProductConnection';
                  orderingMode: Types.ProductOrderingModeEnumApi;
                  defaultOrderingMode: Types.ProductOrderingModeEnumApi | null;
                  totalCount: number;
                  productFilterOptions: {
                      __typename: 'ProductFilterOptions';
                      minimalPrice: string;
                      maximalPrice: string;
                      inStock: number;
                      brands: Array<{
                          __typename: 'BrandFilterOption';
                          count: number;
                          brand: { __typename: 'Brand'; uuid: string; name: string };
                      }> | null;
                      flags: Array<{
                          __typename: 'FlagFilterOption';
                          count: number;
                          isSelected: boolean;
                          flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
                      }> | null;
                      parameters: Array<
                          | {
                                __typename: 'ParameterCheckboxFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterColorFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueColorFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    rgbHex: string | null;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterSliderFilterOption';
                                name: string;
                                uuid: string;
                                minimalValue: number;
                                maximalValue: number;
                                isCollapsed: boolean;
                                selectedValue: number | null;
                                isSelectable: boolean;
                                unit: { __typename: 'Unit'; name: string } | null;
                            }
                      > | null;
                  };
              };
          }
        | {
              __typename: 'Category';
              id: number;
              uuid: string;
              slug: string;
              originalCategorySlug: string | null;
              name: string;
              description: string | null;
              seoH1: string | null;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
              children: Array<{
                  __typename: 'Category';
                  uuid: string;
                  name: string;
                  slug: string;
                  products: { __typename: 'ProductConnection'; totalCount: number };
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
              }>;
              linkedCategories: Array<{
                  __typename: 'Category';
                  uuid: string;
                  name: string;
                  slug: string;
                  products: { __typename: 'ProductConnection'; totalCount: number };
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
              }>;
              products: {
                  __typename: 'ProductConnection';
                  orderingMode: Types.ProductOrderingModeEnumApi;
                  defaultOrderingMode: Types.ProductOrderingModeEnumApi | null;
                  totalCount: number;
                  productFilterOptions: {
                      __typename: 'ProductFilterOptions';
                      minimalPrice: string;
                      maximalPrice: string;
                      inStock: number;
                      brands: Array<{
                          __typename: 'BrandFilterOption';
                          count: number;
                          brand: { __typename: 'Brand'; uuid: string; name: string };
                      }> | null;
                      flags: Array<{
                          __typename: 'FlagFilterOption';
                          count: number;
                          isSelected: boolean;
                          flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
                      }> | null;
                      parameters: Array<
                          | {
                                __typename: 'ParameterCheckboxFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterColorFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueColorFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    rgbHex: string | null;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterSliderFilterOption';
                                name: string;
                                uuid: string;
                                minimalValue: number;
                                maximalValue: number;
                                isCollapsed: boolean;
                                selectedValue: number | null;
                                isSelectable: boolean;
                                unit: { __typename: 'Unit'; name: string } | null;
                            }
                      > | null;
                  };
              };
              readyCategorySeoMixLinks: Array<{ __typename: 'Link'; name: string; slug: string }>;
          }
        | {
              __typename: 'Flag';
              uuid: string;
              slug: string;
              name: string;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
              products: {
                  __typename: 'ProductConnection';
                  orderingMode: Types.ProductOrderingModeEnumApi;
                  defaultOrderingMode: Types.ProductOrderingModeEnumApi | null;
                  totalCount: number;
                  productFilterOptions: {
                      __typename: 'ProductFilterOptions';
                      minimalPrice: string;
                      maximalPrice: string;
                      inStock: number;
                      brands: Array<{
                          __typename: 'BrandFilterOption';
                          count: number;
                          brand: { __typename: 'Brand'; uuid: string; name: string };
                      }> | null;
                      flags: Array<{
                          __typename: 'FlagFilterOption';
                          count: number;
                          isSelected: boolean;
                          flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
                      }> | null;
                      parameters: Array<
                          | {
                                __typename: 'ParameterCheckboxFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterColorFilterOption';
                                name: string;
                                uuid: string;
                                isCollapsed: boolean;
                                values: Array<{
                                    __typename: 'ParameterValueColorFilterOption';
                                    uuid: string;
                                    text: string;
                                    count: number;
                                    rgbHex: string | null;
                                    isSelected: boolean;
                                }>;
                            }
                          | {
                                __typename: 'ParameterSliderFilterOption';
                                name: string;
                                uuid: string;
                                minimalValue: number;
                                maximalValue: number;
                                isCollapsed: boolean;
                                selectedValue: number | null;
                                isSelectable: boolean;
                                unit: { __typename: 'Unit'; name: string } | null;
                            }
                      > | null;
                  };
              };
          }
        | {
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
              variants: Array<{
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
                  storeAvailabilities: Array<{
                      __typename: 'StoreAvailability';
                      exposed: boolean;
                      availabilityInformation: string;
                      availabilityStatus: Types.AvailabilityStatusEnumApi;
                      store: {
                          __typename: 'Store';
                          uuid: string;
                          slug: string;
                          description: string | null;
                          street: string;
                          city: string;
                          postcode: string;
                          contactInfo: string | null;
                          specialMessage: string | null;
                          locationLatitude: string | null;
                          locationLongitude: string | null;
                          storeName: string;
                          country: { __typename: 'Country'; name: string; code: string };
                          openingHours: {
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
                          breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
                          storeImages: Array<{
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
                      } | null;
                  }>;
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
              }>;
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
                        brand: { __typename: 'Brand'; name: string; slug: string } | null;
                        categories: Array<{ __typename: 'Category'; name: string }>;
                    }
              >;
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename?: 'Category'; name: string }>;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              productVideos: Array<{ __typename: 'VideoToken'; description: string; token: string }>;
          }
        | {
              __typename: 'RegularProduct';
              shortDescription: string | null;
              availableStoresCount: number;
              exposedStoresCount: number;
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
              storeAvailabilities: Array<{
                  __typename: 'StoreAvailability';
                  exposed: boolean;
                  availabilityInformation: string;
                  availabilityStatus: Types.AvailabilityStatusEnumApi;
                  store: {
                      __typename: 'Store';
                      uuid: string;
                      slug: string;
                      description: string | null;
                      street: string;
                      city: string;
                      postcode: string;
                      contactInfo: string | null;
                      specialMessage: string | null;
                      locationLatitude: string | null;
                      locationLongitude: string | null;
                      storeName: string;
                      country: { __typename: 'Country'; name: string; code: string };
                      openingHours: {
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
                      breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
                      storeImages: Array<{
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
                  } | null;
              }>;
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
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
                        availability: {
                            __typename: 'Availability';
                            name: string;
                            status: Types.AvailabilityStatusEnumApi;
                        };
                        brand: { __typename: 'Brand'; name: string; slug: string } | null;
                        categories: Array<{ __typename: 'Category'; name: string }>;
                    }
              >;
              brand: { __typename: 'Brand'; name: string; slug: string } | null;
              categories: Array<{ __typename?: 'Category'; name: string }>;
              flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
              availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
              productVideos: Array<{ __typename: 'VideoToken'; description: string; token: string }>;
          }
        | {
              __typename: 'Store';
              uuid: string;
              slug: string;
              description: string | null;
              street: string;
              city: string;
              postcode: string;
              contactInfo: string | null;
              specialMessage: string | null;
              locationLatitude: string | null;
              locationLongitude: string | null;
              storeName: string;
              country: { __typename: 'Country'; name: string; code: string };
              openingHours: {
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
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
              storeImages: Array<{
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
          }
        | { __typename: 'Variant'; mainVariant: { __typename?: 'MainVariant'; slug: string } | null }
        | null;
};

export const SlugQueryDocumentApi = gql`
    query SlugQuery($slug: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
        slug(slug: $slug) {
            __typename
            ... on RegularProduct {
                ...ProductDetailFragment
            }
            ... on Variant {
                mainVariant {
                    slug
                }
            }
            ... on MainVariant {
                ...MainVariantDetailFragment
            }
            ... on Category {
                ...CategoryDetailFragment
            }
            ... on Store {
                ...StoreDetailFragment
            }
            ... on ArticleSite {
                ...ArticleDetailFragment
            }
            ... on BlogArticle {
                ...BlogArticleDetailFragment
            }
            ... on Brand {
                ...BrandDetailFragment
            }
            ... on Flag {
                ...FlagDetailFragment
            }
            ... on BlogCategory {
                ...BlogCategoryDetailFragment
            }
        }
    }
    ${ProductDetailFragmentApi}
    ${MainVariantDetailFragmentApi}
    ${CategoryDetailFragmentApi}
    ${StoreDetailFragmentApi}
    ${ArticleDetailFragmentApi}
    ${BlogArticleDetailFragmentApi}
    ${BrandDetailFragmentApi}
    ${FlagDetailFragmentApi}
    ${BlogCategoryDetailFragmentApi}
`;

export function useSlugQueryApi(options: Omit<Urql.UseQueryArgs<SlugQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<SlugQueryApi, SlugQueryVariablesApi>({ query: SlugQueryDocumentApi, ...options });
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
