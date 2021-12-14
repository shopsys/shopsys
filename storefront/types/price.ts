export type PriceApiType = {
    priceWithVat: string;
    priceWithoutVat: string;
    vatAmount: string;
};

export type PriceType = {
    priceWithVat: number;
    priceWithoutVat: number;
    vatAmount: number;
    currencyCode: string;
};
