import { ImageType } from 'types/image';
import { ProductPriceType } from 'components/Blocks/Product/types';

export type AddToCartPopupDataType = {
    name: string;
    slug: string;
    image: ImageType | null;
    quantity: number;
    unitName: string;
    price: ProductPriceType;
};
