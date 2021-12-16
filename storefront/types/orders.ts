import { ImageType } from './image';
import { PageInfoType } from './product';
import { PriceType } from './price';

export type ListedOrderType = {
    number: string;
    creationDate: string;
    items: {
        quantity: number;
    };
    transport: {
        name: string;
        image: ImageType | null;
    };
    payment: string;
    totalPrice: PriceType;
};

export type ListedOrdersType = {
    orders: ListedOrderType[];
    totalCount: number;
    pageInfo: PageInfoType;
};
