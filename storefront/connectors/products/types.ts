import { ImageType } from 'components/Basic/Image/types';
import { PriceType } from 'connectors/transports/types';

export type SimpleProductType = {
    __typename?: 'MainVariant' | 'RegularProduct' | 'Variant';
    slug: string;
    name: string;
    price: PriceType;
    image: ImageType | null;
};
