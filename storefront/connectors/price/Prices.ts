import { PriceFragmentApi, PriceInterfaceApi, ProductPriceFragmentApi } from 'graphql/generated';
import { PriceType, ProductPriceType } from 'types/price';

export const mapPriceData = (price: PriceInterfaceApi, currencyCode: string): PriceType => {
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
        __typename: 'Price',
    };
};

export const mapProductPriceData = (
    price: ProductPriceFragmentApi['price'],
    currencyCode: string,
): ProductPriceType => {
    return {
        ...mapPriceData(price, currencyCode),
        isPriceFrom: price.isPriceFrom,
    };
};
