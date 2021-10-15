import { OrderApiType, OrderInputType } from './types';
import { useMutation, UseMutationResponse } from 'urql';
import { paymentBody } from 'connectors/payments/Payment';
import { transportBody } from 'connectors/transports/Transport';

export const createOrderMutation = `mutation (
        $firstName: String! 
        $lastName: String! 
        $email: String! 
        $telephone: String! 
        $onCompanyBehalf: Boolean! 
        $companyName: String 
        $companyNumber: String 
        $companyTaxNumber: String 
        $street: String! 
        $city: String! 
        $postcode: String! 
        $country: String! 
        $differentDeliveryAddress: Boolean! 
        $deliveryFirstName: String 
        $deliveryLastName: String 
        $deliveryCompanyName: String 
        $deliveryTelephone: String 
        $deliveryStreet: String 
        $deliveryCity: String 
        $deliveryPostcode: String 
        $deliveryCountry: String 
        $note: String 
        $payment: PaymentInput! 
        $transport: TransportInput! 
        $cartUuid: Uuid 
        $promoCode: String 
    ) {
        CreateOrder(input: {
            firstName: $firstName
            lastName: $lastName
            email: $email
            telephone: $telephone
            onCompanyBehalf: $onCompanyBehalf
            companyName: $companyName
            companyNumber: $companyNumber
            companyTaxNumber: $companyTaxNumber
            street: $street
            city: $city
            postcode: $postcode
            country: $country
            differentDeliveryAddress: $differentDeliveryAddress
            deliveryFirstName: $deliveryFirstName
            deliveryLastName: $deliveryLastName
            deliveryCompanyName: $deliveryCompanyName
            deliveryTelephone: $deliveryTelephone
            deliveryStreet: $deliveryStreet
            deliveryCity: $deliveryCity
            deliveryPostcode: $deliveryPostcode
            deliveryCountry: $deliveryCountry
            note: $note
            payment: $payment
            transport: $transport
            cartUuid: $cartUuid
            promoCode: $promoCode
        }) {
            uuid
            number
            creationDate
            items {
                name
                unitPrice {
                  priceWithVat
                  priceWithoutVat
                  vatAmount
                }
                totalPrice {
                  priceWithVat
                  priceWithoutVat
                  vatAmount
                }
                vatRate
                quantity
                unit
            }
            ${transportBody}
            ${paymentBody}
            status
            totalPrice {
                priceWithVat
                priceWithoutVat
                vatAmount
            }
            firstName
            lastName
            email
            telephone
            companyName
            companyNumber
            companyTaxNumber
            street
            city
            postcode
            country
            differentDeliveryAddress
            deliveryFirstName
            deliveryLastName
            deliveryCompanyName
            deliveryTelephone
            deliveryStreet
            deliveryCity
            deliveryPostcode
            deliveryCountry
            note
            urlHash
            promoCode
            trackingNumber
            trackingUrl
        }
    }` as const;

export const useCreateOrder = (): UseMutationResponse<{ CreateOrder: OrderApiType }, OrderInputType> => {
    return useMutation(createOrderMutation);
};
