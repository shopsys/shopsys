import { CountryType } from 'types/country';

export type PickupPlaceType = {
    identifier: string;
    name: string;
    description: string;
    openingHours: string;
    street: string;
    postcode: string;
    city: string;
    country: CountryType;
};
