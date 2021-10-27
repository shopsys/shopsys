import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';

export type StoreDetailType = {
    __typename: string;
    slug: string;
    uuid: string;
    storeName: string;
    description: string | null;
    street: string;
    city: string;
    postcode: string;
    country: string;
    openingHours: string | null;
    contactInfo: string | null;
    specialMessage: string | null;
    locationLatitude: number | null;
    locationLongitude: number | null;
    breadcrumb: BreadcrumbItemType[];
};

export type StoreDetailApiType = {
    __typename: string;
    slug: string;
    uuid: string;
    storeName: string;
    description: string | null;
    street: string;
    city: string;
    postcode: string;
    country: string;
    openingHours: string | null;
    contactInfo: string | null;
    specialMessage: string | null;
    locationLatitude: string | null;
    locationLongitude: string | null;
    breadcrumb: BreadcrumbItemType[];
};
