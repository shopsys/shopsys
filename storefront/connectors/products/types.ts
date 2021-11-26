import { FlagType, ProductPriceType } from 'components/Blocks/Product/types';
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
