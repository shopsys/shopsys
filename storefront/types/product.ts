import { AvailabilityType, StoreAvailabilityType } from 'types/availability';
import { ImageSizesType, ImageSizeType } from 'types/image';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { FilterOptionsType } from 'types/productFilter';
import { PageInfoType } from 'types/pageInfo';
import { ProductPriceType } from 'types/price';
import { SimpleFlagType } from 'types/flag';
import { StoreDetailType } from 'types/store';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    slug: string;
    name: string;
    price: ProductPriceType;
    image: ImageSizeType | null;
    unitName: string;
};

export type ListedProductConnectionType = {
    products: ListedProductType[];
    productFilterOptions: FilterOptionsType | null;
    totalCount: number;
    pageInfo: PageInfoType;
};

export type ListedProductType = {
    uuid: string;
    slug: string;
    name: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: SimpleFlagType[];
    availability: string;
    image: ImageSizeType | null;
    price: ProductPriceType;
    isMainVariant: boolean;
    catalogNumber: string;
};

export type ListedVariantType = {
    uuid: string;
    slug: string;
    name: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: SimpleFlagType[];
    availability: string;
    images: ImageSizesType[];
    price: ProductPriceType;
    catalogNumber: string;
    storeAvailabilities: StoreAvailability[];
};

export type MainVariantDetailType = {
    __typename: 'MainVariant';
    breadcrumb: BreadcrumbItemType[];
    uuid: string;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    stockQuantity: number;
    description: string;
    catalogNumber: string;
    price: ProductPriceType;
    accessories: ListedProductType[];
    parameters: ProductParameterType[];
    images: ImageSizesType[];
    variants: ListedVariantType[];
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
    shortDescription: string;
    catalogNumber: string;
    price: ProductPriceType;
    availability: Availability;
    storeAvailabilities: StoreAvailability[];
    availableStoresCount: number;
    exposedStoresCount: number;
    accessories: SliderProductItemType[];
    parameters: ProductParameterType[];
    images: ImageSizesType[];
};

export type SliderProductItemType = {
    uuid: string;
    slug: string;
    name: string;
    flags: SimpleFlagType[];
    image: ImageSizeType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
};

export type ListedProductItemType = {
    uuid: string;
    slug: string;
    name: string;
    flags: SimpleFlagType[];
    image: ImageSizeType | null;
    stockQuantity: number;
    price: ProductPriceType;
    isMainVariant: boolean;
    availability: string;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
};

export type ListedProductEdgesType = {
    edges: {
        node: ListedProductItemType;
    }[];
    productFilterOptions: FilterOptionsType | null;
    totalCount: number;
    pageInfo: PageInfoType;
};
