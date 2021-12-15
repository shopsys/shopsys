import { ProductPriceType, SliderProductItemType } from 'components/Blocks/Product/types';
import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';
import { ImageType } from 'components/Basic/Image/types';

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

export type ProductParameterType = {
    uuid: string;
    name: string;
    visible: boolean;
    values: {
        uuid: string;
        text: string;
    }[];
};

export type ProductDetailImageType = {
    [sizeName: string]: ImageType;
};

export type ProductDetailType = {
    __typename: 'MainVariant' | 'RegularProduct' | 'Variant';
    uuid: string;
    name: string;
    slug: string;
    breadcrumb: BreadcrumbItemType[];
    namePrefix: string;
    nameSuffix: string;
    stockQuantity: number;
    description: string;
    catalogNumber: string;
    price: ProductPriceType;
    availability: Availability;
    storeAvailabilities: StoreAvailability[];
    availableStoresCount: number;
    exposedStoresCount: number;
    accessories: SliderProductItemType[];
    parameters: ProductParameterType[];
    images: ProductDetailImageType[];
};
