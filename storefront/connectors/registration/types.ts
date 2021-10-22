export type RegistrationInputType = {
    firstName: string;
    lastName: string;
    email: string;
    password: string;
    telephone: string;
    street: string;
    city: string;
    postcode: string;
    country: string;
    companyCustomer: boolean;
    companyName: string | null;
    companyNumber: string | null;
    companyTaxNumber: string | null;
    newsletterSubscription: boolean;
};
