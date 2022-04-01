import { SimpleBrandType } from './brand';
import { ProductCartItemType } from './cart';
import { FilterOptionsType } from './productFilter';
import { AvailabilityType, StoreAvailabilityType } from 'types/availability';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { SimpleFlagType } from 'types/flag';
import { ImageType } from 'types/image';
import { PageInfoType } from 'types/pageInfo';
import { ProductParameterType } from 'types/parameter';
import { ProductPriceType } from 'types/price';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    uuid: string;
    catalogNumber: string;
    slug: string;
    fullName: string;
    price: ProductPriceType;
    image: ImageType | null;
    unitName: string;
    brand: SimpleBrandType | null;
    categoryNames: string[];
    flags: SimpleFlagType[];
    availability: AvailabilityType;
};

export type SimpleProductConnectionType = {
    totalCount: number;
    products: SimpleProductType[];
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
    fullName: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: SimpleFlagType[];
    image: ImageType | null;
    availability: AvailabilityType;
    price: ProductPriceType;
    isMainVariant: boolean;
    catalogNumber: string;
    brand: SimpleBrandType | null;
    categoryNames: string[];
};

export type ListedVariantType = ListedProductType & {
    storeAvailabilities: StoreAvailabilityType[];
};

export type SliderProductItemType = ListedProductType;

export type ProductDetailInterfaceType = {
    uuid: string;
    slug: string;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    fullName: string;
    breadcrumb: BreadcrumbItemType[];
    catalogNumber: string;
    description: string;
    images: ImageType[];
    price: ProductPriceType;
    parameters: ProductParameterType[];
    stockQuantity: number;
    accessories: SliderProductItemType[];
    brand: SimpleBrandType | null;
    categoryNames: string[];
    flags: SimpleFlagType[];
    isSellingDenied: boolean;
    availability: AvailabilityType;
};

export type ProductDetailType = ProductDetailInterfaceType & {
    __typename: 'MainVariant' | 'RegularProduct' | 'Variant';
    shortDescription: string;
    storeAvailabilities: StoreAvailabilityType[];
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type MainVariantDetailType = ProductDetailInterfaceType & {
    __typename: 'MainVariant';
    variants: ListedVariantType[];
};

export type ProductInterfaceType =
    | ProductDetailType
    | MainVariantDetailType
    | ProductCartItemType
    | ListedProductType
    | SimpleProductType;
