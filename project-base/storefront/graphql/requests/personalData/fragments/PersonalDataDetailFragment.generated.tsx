// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from '../../orders/fragments/OrderDetailItemFragment.generated';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { BaseCustomerUserFragment } from '../../customer/fragments/BaseCustomerUserFragment.generated';
export type TypePersonalDataDetailFragment = (
  { __typename: 'PersonalData' }
  & Pick<Types.TypePersonalData, 'exportLink'>
  & { orders: Array<(
    { __typename: 'Order' }
    & Pick<Types.TypeOrder, 'uuid' | 'city' | 'companyName' | 'number' | 'creationDate' | 'firstName' | 'lastName' | 'telephone' | 'companyNumber' | 'companyTaxNumber' | 'street' | 'postcode' | 'deliveryFirstName' | 'deliveryLastName' | 'deliveryCompanyName' | 'deliveryTelephone' | 'deliveryStreet' | 'deliveryCity' | 'deliveryPostcode'>
    & { items: Array<(
      { __typename: 'OrderItem' }
      & Pick<Types.TypeOrderItem, 'uuid' | 'name' | 'vatRate' | 'quantity' | 'unit' | 'type'>
      & { unitPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), totalPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), order: (
        { __typename?: 'Order' }
        & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate'>
        & { customerUser: Types.Maybe<(
          { __typename?: 'CompanyCustomerUser' }
          & Pick<Types.TypeCompanyCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'CurrentCompanyCustomerUser' }
          & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'CurrentRegularCustomerUser' }
          & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'RegularCustomerUser' }
          & Pick<Types.TypeRegularCustomerUser, 'uuid'>
        )>, withdrawalRequest: Types.Maybe<{ __typename: 'OrderWithdrawalRequest' }> }
      ), product: Types.Maybe<(
        { __typename?: 'MainVariant' }
        & Pick<Types.TypeMainVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      ) | (
        { __typename?: 'RegularProduct' }
        & Pick<Types.TypeRegularProduct, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      ) | (
        { __typename?: 'Variant' }
        & Pick<Types.TypeVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      )>, transport: Types.Maybe<(
        { __typename?: 'Transport' }
        & Pick<Types.TypeTransport, 'isPersonalPickup' | 'transportTypeCode'>
        & { mainImage: Types.Maybe<(
          { __typename?: 'Image' }
          & Pick<Types.TypeImage, 'url'>
        )> }
      )>, payment: Types.Maybe<(
        { __typename?: 'Payment' }
        & { mainImage: Types.Maybe<(
          { __typename?: 'Image' }
          & Pick<Types.TypeImage, 'url'>
        )> }
      )> }
    )>, country: (
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    ), deliveryCountry: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, productItems: Array<(
      { __typename: 'OrderItem' }
      & Pick<Types.TypeOrderItem, 'uuid' | 'name' | 'vatRate' | 'quantity' | 'unit' | 'type'>
      & { unitPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), totalPrice: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), order: (
        { __typename?: 'Order' }
        & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate'>
        & { customerUser: Types.Maybe<(
          { __typename?: 'CompanyCustomerUser' }
          & Pick<Types.TypeCompanyCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'CurrentCompanyCustomerUser' }
          & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'CurrentRegularCustomerUser' }
          & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid'>
        ) | (
          { __typename?: 'RegularCustomerUser' }
          & Pick<Types.TypeRegularCustomerUser, 'uuid'>
        )>, withdrawalRequest: Types.Maybe<{ __typename: 'OrderWithdrawalRequest' }> }
      ), product: Types.Maybe<(
        { __typename?: 'MainVariant' }
        & Pick<Types.TypeMainVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      ) | (
        { __typename?: 'RegularProduct' }
        & Pick<Types.TypeRegularProduct, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      ) | (
        { __typename?: 'Variant' }
        & Pick<Types.TypeVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
        & { categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, price: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), giftPrice: (
          { __typename: 'ProductPrice' }
          & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
          & { basicPrice: (
            { __typename?: 'Price' }
            & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
          ) }
        ), availability: (
          { __typename?: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ) }
      )>, transport: Types.Maybe<(
        { __typename?: 'Transport' }
        & Pick<Types.TypeTransport, 'isPersonalPickup' | 'transportTypeCode'>
        & { mainImage: Types.Maybe<(
          { __typename?: 'Image' }
          & Pick<Types.TypeImage, 'url'>
        )> }
      )>, payment: Types.Maybe<(
        { __typename?: 'Payment' }
        & { mainImage: Types.Maybe<(
          { __typename?: 'Image' }
          & Pick<Types.TypeImage, 'url'>
        )> }
      )> }
    )>, totalPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'currencyCode'>
    ) }
  )>, customerUser: Types.Maybe<(
    { __typename: 'CompanyCustomerUser' }
    & Pick<Types.TypeCompanyCustomerUser, 'companyName' | 'companyNumber' | 'companyTaxNumber' | 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, deliveryAddresses: Array<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ), salesRepresentative: Types.Maybe<(
      { __typename: 'SalesRepresentative' }
      & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
      & { image: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url' | 'name'>
      )>, telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )> }
    )> }
  ) | (
    { __typename: 'CurrentCompanyCustomerUser' }
    & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, deliveryAddresses: Array<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ), salesRepresentative: Types.Maybe<(
      { __typename: 'SalesRepresentative' }
      & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
      & { image: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url' | 'name'>
      )>, telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )> }
    )> }
  ) | (
    { __typename: 'CurrentRegularCustomerUser' }
    & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, deliveryAddresses: Array<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ), salesRepresentative: Types.Maybe<(
      { __typename: 'SalesRepresentative' }
      & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
      & { image: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url' | 'name'>
      )>, telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )> }
    )> }
  ) | (
    { __typename: 'RegularCustomerUser' }
    & Pick<Types.TypeRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )>, defaultDeliveryAddress: Types.Maybe<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, deliveryAddresses: Array<(
      { __typename: 'DeliveryAddress' }
      & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
      & { telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )>, country: Types.Maybe<(
        { __typename: 'Country' }
        & Pick<Types.TypeCountry, 'name' | 'code'>
      )> }
    )>, roleGroup: (
      { __typename: 'CustomerUserRoleGroup' }
      & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
    ), salesRepresentative: Types.Maybe<(
      { __typename: 'SalesRepresentative' }
      & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
      & { image: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url' | 'name'>
      )>, telephoneData: Types.Maybe<(
        { __typename?: 'PhoneData' }
        & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
      )> }
    )> }
  )>, newsletterSubscriber: Types.Maybe<(
    { __typename: 'NewsletterSubscriber' }
    & Pick<Types.TypeNewsletterSubscriber, 'email' | 'createdAt'>
  )>, complaints: Array<(
    { __typename: 'Complaint' }
    & Pick<Types.TypeComplaint, 'uuid' | 'number' | 'createdAt' | 'status' | 'deliveryFirstName' | 'deliveryLastName' | 'deliveryCompanyName' | 'deliveryCity' | 'deliveryPostcode' | 'deliveryStreet' | 'deliveryTelephone'>
    & { deliveryCountry: (
      { __typename?: 'Country' }
      & Pick<Types.TypeCountry, 'name'>
    ), items: Array<(
      { __typename: 'ComplaintItem' }
      & Pick<Types.TypeComplaintItem, 'productName' | 'quantity' | 'description'>
      & { orderItem: Types.Maybe<(
        { __typename?: 'OrderItem' }
        & Pick<Types.TypeOrderItem, 'uuid'>
      )> }
    )> }
  )> }
);

export const PersonalDataDetailFragment = gql`
    fragment PersonalDataDetailFragment on PersonalData {
  __typename
  orders {
    __typename
    uuid
    city
    companyName
    number
    creationDate
    items {
      ...OrderDetailItemFragment
    }
    firstName
    lastName
    telephone
    companyNumber
    companyTaxNumber
    street
    city
    postcode
    country {
      ...CountryFragment
    }
    deliveryFirstName
    deliveryLastName
    deliveryCompanyName
    deliveryTelephone
    deliveryStreet
    deliveryCity
    deliveryPostcode
    deliveryCountry {
      ...CountryFragment
    }
    productItems {
      ...OrderDetailItemFragment
    }
    totalPrice {
      priceWithVat
      currencyCode
    }
  }
  customerUser {
    ...BaseCustomerUserFragment
  }
  newsletterSubscriber {
    __typename
    email
    createdAt
  }
  exportLink
  complaints {
    __typename
    uuid
    number
    createdAt
    status
    deliveryFirstName
    deliveryLastName
    deliveryCompanyName
    deliveryCity
    deliveryPostcode
    deliveryStreet
    deliveryTelephone
    deliveryCountry {
      name
    }
    items {
      __typename
      productName
      quantity
      description
      orderItem {
        uuid
      }
    }
  }
}
    ${OrderDetailItemFragment}
${CountryFragment}
${BaseCustomerUserFragment}`;