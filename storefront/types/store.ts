import { BreadcrumbItemType } from 'types/breadcrumb';

export type StoreDetailType = {
    __typename: 'Store';
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

export type StoreListType = {
    slug: string;
    name: string;
    locationLatitude: number | null;
    locationLongitude: number | null;
    address: string;
    openingHours?: string | null;
};
