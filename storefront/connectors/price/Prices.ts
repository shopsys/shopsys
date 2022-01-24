import { PriceFragmentApi } from 'graphql/generated';
import { PriceType } from 'types/price';

export const mapPriceData = (price: PriceFragmentApi, currencyCode: string): PriceType => {
    return {
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        currencyCode,
    };
};

export const mapPriceInputData = (price: PriceType): PriceFragmentApi => {
    return {
        priceWithVat: price.priceWithVat.toString(),
        priceWithoutVat: price.priceWithoutVat.toString(),
        vatAmount: price.vatAmount.toString(),
    };
};
