import { ProductItemApiType, SliderProductItemType } from 'components/Blocks/Product/types';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from '../../../connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export type Availability = {
    name: string;
    status: 'in-stock' | 'out-of-stock';
};

export type StoreAvailability = {
    storeName: string;
    exposed: boolean;
    availabilityInformation: string;
    availabilityStatus: 'in-stock' | 'out-of-stock';
};
export interface ProductDetailApiType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    description: string;
    catalogNumber: string;
    availability: Availability;
    storeAvailabilities: StoreAvailability[];
    availableStoresCount: number;
    exposedStoresCount: number;
    accessories: ProductItemApiType[];
}

export interface ProductDetailType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    description: string;
    catalogNumber: string;
    availability: Availability;
    storeAvailabilities: StoreAvailability[];
    availableStoresCount: number;
    exposedStoresCount: number;
    accessories: SliderProductItemType[];
}
