import { FlagType, ProductPriceType } from 'components/Blocks/Product/types';
import { ProductDetailImageType, ProductParameterType, StoreAvailability } from 'components/Pages/ProductDetail/types';
import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';
import { ImageType } from 'components/Basic/Image/types';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    slug: string;
    name: string;
    price: ProductPriceType;
    image: ImageType | null;
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
