export type PriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    currencyCode: string;
};

export type ProductPriceType = PriceType & {
    isPriceFrom: boolean;
};
