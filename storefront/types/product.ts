import { AvailabilityType, StoreAvailabilityType } from 'types/availability';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { SimpleFlagType } from 'types/flag';
import { ImageType } from 'types/image';
import { PageInfoType } from 'types/pageInfo';
import { ProductParameterType } from 'types/parameter';
import { ProductPriceType } from 'types/price';
import { FilterOptionsType } from 'types/productFilter';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    slug: string;
    fullName: string;
    price: ProductPriceType;
    image: ImageType | null;
    unitName: string;
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
    name: string;
    stockQuantity: number;
    availableStoresCount: number;
    exposedStoresCount: number;
    flags: SimpleFlagType[];
    availability: string;
    image: ImageType | null;
    price: ProductPriceType;
    isMainVariant: boolean;
    catalogNumber: string;
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
    breadcrumb: BreadcrumbItemType[];
    catalogNumber: string;
    description: string;
    images: ImageType[];
    price: ProductPriceType;
    parameters: ProductParameterType[];
    stockQuantity: number;
    accessories: SliderProductItemType[];
    flags: SimpleFlagType[];
    isSellingDenied: boolean;
};

export type ProductDetailType = ProductDetailInterfaceType & {
    __typename: 'MainVariant' | 'RegularProduct' | 'Variant';
    shortDescription: string;
    availability: AvailabilityType;
    storeAvailabilities: StoreAvailabilityType[];
    availableStoresCount: number;
    exposedStoresCount: number;
};

export type MainVariantDetailType = ProductDetailInterfaceType & {
    __typename: 'MainVariant';
    variants: ListedVariantType[];
};
