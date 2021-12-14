import { FlagType, ProductPriceType } from 'components/Blocks/Product/types';
import { PriceType, TransportInputType } from 'connectors/transports/types';
import { ImageType } from 'components/Basic/Image/types';
import { PaymentInputType } from 'types/payment';

export type CartInput = {
    cartUuid: string | null;
    isCartEmpty: boolean;
    transport: TransportInputType | null;
    payment: PaymentInputType | null;
    promoCode: string | null;
};

export type ProductCartItemType = {
    uuid: string;
    slug: string;
    fullName: string;
    flags: FlagType[];
    image: ImageType | null;
    price: ProductPriceType;
    availability: string;
    stockQuantity: number;
    availableStoresCount: number;
    catalogNumber: string;
    unit: {
        name: string;
    };
};

export type CartItemType = {
    uuid: string;
    product: ProductCartItemType;
    quantity: number;
};

export type CartType = {
    uuid: string | null;
    items: CartItemType[];
    totalPrice: PriceType;
    totalDiscountPrice: PriceType;
    remainingAmountWithVatForFreeTransport: number | null;
};
