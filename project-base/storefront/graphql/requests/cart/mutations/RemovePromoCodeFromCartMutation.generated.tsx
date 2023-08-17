import * as Types from '../../types';

import gql from 'graphql-tag';
import { CartFragmentApi } from '../fragments/CartFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type RemovePromoCodeFromCartMutationVariablesApi = Types.Exact<{
    input: Types.RemovePromoCodeFromCartInputApi;
}>;

export type RemovePromoCodeFromCartMutationApi = {
    __typename?: 'Mutation';
    RemovePromoCodeFromCart: {
        __typename: 'Cart';
        uuid: string | null;
        remainingAmountWithVatForFreeTransport: string | null;
        promoCode: string | null;
        selectedPickupPlaceIdentifier: string | null;
        paymentGoPayBankSwift: string | null;
        items: Array<{
            __typename: 'CartItem';
            uuid: string;
            quantity: number;
            product:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
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
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
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
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
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
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  };
        }>;
        totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
        totalItemsPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
        totalDiscountPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
        modifications: {
            __typename: 'CartModificationsResult';
            someProductWasRemovedFromEshop: boolean;
            itemModifications: {
                __typename: 'CartItemModificationsResult';
                noLongerListableCartItems: Array<{
                    __typename: 'CartItem';
                    uuid: string;
                    quantity: number;
                    product:
                        | {
                              __typename: 'MainVariant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'RegularProduct';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'Variant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          };
                }>;
                cartItemsWithModifiedPrice: Array<{
                    __typename: 'CartItem';
                    uuid: string;
                    quantity: number;
                    product:
                        | {
                              __typename: 'MainVariant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'RegularProduct';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'Variant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          };
                }>;
                cartItemsWithChangedQuantity: Array<{
                    __typename: 'CartItem';
                    uuid: string;
                    quantity: number;
                    product:
                        | {
                              __typename: 'MainVariant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'RegularProduct';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'Variant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          };
                }>;
                noLongerAvailableCartItemsDueToQuantity: Array<{
                    __typename: 'CartItem';
                    uuid: string;
                    quantity: number;
                    product:
                        | {
                              __typename: 'MainVariant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'RegularProduct';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          }
                        | {
                              __typename: 'Variant';
                              id: number;
                              uuid: string;
                              slug: string;
                              fullName: string;
                              catalogNumber: string;
                              stockQuantity: number;
                              availableStoresCount: number;
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
                              availability: {
                                  __typename: 'Availability';
                                  name: string;
                                  status: Types.AvailabilityStatusEnumApi;
                              };
                              price: {
                                  __typename: 'ProductPrice';
                                  priceWithVat: string;
                                  priceWithoutVat: string;
                                  vatAmount: string;
                                  isPriceFrom: boolean;
                              };
                              unit: { __typename?: 'Unit'; name: string };
                              brand: { __typename: 'Brand'; name: string; slug: string } | null;
                              categories: Array<{ __typename?: 'Category'; name: string }>;
                          };
                }>;
            };
            transportModifications: {
                __typename: 'CartTransportModificationsResult';
                transportPriceChanged: boolean;
                transportUnavailable: boolean;
                transportWeightLimitExceeded: boolean;
                personalPickupStoreUnavailable: boolean;
            };
            paymentModifications: {
                __typename: 'CartPaymentModificationsResult';
                paymentPriceChanged: boolean;
                paymentUnavailable: boolean;
            };
            promoCodeModifications: {
                __typename: 'CartPromoCodeModificationsResult';
                noLongerApplicablePromoCode: Array<string>;
            };
        };
        transport: {
            __typename: 'Transport';
            uuid: string;
            name: string;
            description: string | null;
            instruction: string | null;
            daysUntilDelivery: number;
            isPersonalPickup: boolean;
            price: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
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
            payments: Array<{
                __typename: 'Payment';
                uuid: string;
                name: string;
                description: string | null;
                instruction: string | null;
                type: string;
                price: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
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
                goPayPaymentMethod: {
                    __typename: 'GoPayPaymentMethod';
                    identifier: string;
                    name: string;
                    paymentGroup: string;
                } | null;
            }>;
            stores: {
                __typename: 'StoreConnection';
                edges: Array<{
                    __typename: 'StoreEdge';
                    node: {
                        __typename: 'Store';
                        slug: string;
                        name: string;
                        description: string | null;
                        locationLatitude: string | null;
                        locationLongitude: string | null;
                        street: string;
                        postcode: string;
                        city: string;
                        identifier: string;
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
                        country: { __typename: 'Country'; name: string; code: string };
                    } | null;
                } | null> | null;
            } | null;
            transportType: { __typename: 'TransportType'; code: string };
        } | null;
        payment: {
            __typename: 'Payment';
            uuid: string;
            name: string;
            description: string | null;
            instruction: string | null;
            type: string;
            price: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
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
            goPayPaymentMethod: {
                __typename: 'GoPayPaymentMethod';
                identifier: string;
                name: string;
                paymentGroup: string;
            } | null;
        } | null;
    };
};

export const RemovePromoCodeFromCartMutationDocumentApi = gql`
    mutation RemovePromoCodeFromCartMutation($input: RemovePromoCodeFromCartInput!) {
        RemovePromoCodeFromCart(input: $input) {
            ...CartFragment
        }
    }
    ${CartFragmentApi}
`;

export function useRemovePromoCodeFromCartMutationApi() {
    return Urql.useMutation<RemovePromoCodeFromCartMutationApi, RemovePromoCodeFromCartMutationVariablesApi>(
        RemovePromoCodeFromCartMutationDocumentApi,
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
