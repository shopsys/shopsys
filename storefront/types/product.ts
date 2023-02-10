import { SimpleBrandType } from './brand';
import { ProductCartItemType } from './cart';
import { FilterOptionsType } from './productFilter';
import {
    AvailabilityFragmentApi,
    ImageSizesFragmentApi,
    ProductOrderingModeEnumApi,
    ProductPriceFragmentApi,
    StoreAvailabilityFragmentApi,
} from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { SimpleFlagType } from 'types/flag';
import { PageInfoType } from 'types/pageInfo';
import { ProductParameterType } from 'types/parameter';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    id: number;
    uuid: string;
    catalogNumber: string;
    slug: string;
    fullName: string;
    price: ProductPriceFragmentApi;
    image: ImageSizesFragmentApi | null;
    unitName: string;
    brand: SimpleBrandType | null;
    categoryNames: string[];
    flags: SimpleFlagType[];
    availability: AvailabilityFragmentApi;
};

export type SimpleProductConnectionType = {
    totalCount: number;
    products: SimpleProductType[];
};

export type ListedProductConnectionPreviewType = {
    productFilterOptions: FilterOptionsType | null;
    orderingMode: ProductOrderingModeEnumApi | null;
    defaultOrderingMode: ProductOrderingModeEnumApi | null;
    totalCount: number;
};

export type ListedProductConnectionType = ListedProductConnectionPreviewType & {
    products: ListedProductType[];
    pageInfo: PageInfoType;
};

export type ListedProductType = {
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: SimpleFlagType[];
    image: ImageSizesFragmentApi | null;
    availability: AvailabilityFragmentApi;
    price: ProductPriceFragmentApi;
    isMainVariant: boolean;
    catalogNumber: string;
    brand: SimpleBrandType | null;
    categoryNames: string[];
    isSellingDenied: boolean;
};

export type ListedVariantType = ListedProductType & {
    storeAvailabilities: StoreAvailabilityFragmentApi[];
};

export type SliderProductItemType = ListedProductType;

export type ProductDetailInterfaceType = {
    id: number;
    uuid: string;
    slug: string;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    fullName: string;
    breadcrumb: BreadcrumbItemType[];
    catalogNumber: string;
    ean: string | null;
    description: string;
    images: ImageSizesFragmentApi[];
    price: ProductPriceFragmentApi;
    parameters: ProductParameterType[];
    stockQuantity: number;
    accessories: SliderProductItemType[];
    brand: SimpleBrandType | null;
    categoryNames: string[];
    flags: SimpleFlagType[];
    isSellingDenied: boolean;
    availability: AvailabilityFragmentApi;
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type ProductDetailType = ProductDetailInterfaceType & {
    __typename: 'MainVariant' | 'RegularProduct' | 'Variant';
    shortDescription: string;
    storeAvailabilities: StoreAvailabilityFragmentApi[];
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
