import { CountryType } from 'types/country';

export type PickupPlaceType = {
    identifier: string;
    name: string;
    description: string;
    openingHoursHtml: string;
    street: string;
    postcode: string;
    city: string;
    country: CountryType;
};
