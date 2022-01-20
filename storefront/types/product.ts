import { BreadcrumbItemType } from 'types/breadcrumb';
import { FilterOptionsType } from 'types/productFilter';
import { ImageType } from 'types/image';
import { StoreDetailType } from 'types/store';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    slug: string;
    name: string;
    price: ProductPriceType;
    image: ImageType | null;
    unitName: string;
};

export type ListedProductType = {
    uuid: string;
    slug: string;
    name: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: FlagType[];
    availability: string;
    image: ImageType | null;
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
    flags: FlagType[];
    availability: string;
    images: ProductDetailImageType[];
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
    images: ProductDetailImageType[];
    variants: ListedVariantType[];
};

export type Availability = {
    name: string;
    status: 'in-stock' | 'out-of-stock';
};

export type StoreAvailability = {
    exposed: boolean;
    availabilityInformation: string;
    availabilityStatus: 'in-stock' | 'out-of-stock';
    store: StoreDetailType;
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
    shortDescription: string;
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

export type FlagType = {
    name: string;
    rgbColor: string;
};

export type ProductPriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    isPriceFrom: boolean;
    currencyCode: string;
};

export type PageInfoType = {
    startCursor: string;
    endCursor: string;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
};

export type SliderProductItemType = {
    uuid: string;
    slug: string;
    name: string;
    flags: FlagType[];
    image: ImageType | null;
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
    flags: FlagType[];
    image: ImageType | null;
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
