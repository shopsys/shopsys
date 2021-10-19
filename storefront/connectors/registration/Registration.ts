import { useMutation, UseMutationResponse } from 'urql';
import { RegistrationInputType } from './types';

export const registerMutation = `mutation (
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
    ) {
        Register(input: {
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
        }){
            accessToken
        }
    }` as const;

export const useRegister = (): UseMutationResponse<{ Register: { accessToken: string } }, RegistrationInputType> => {
    return useMutation(registerMutation);
};
