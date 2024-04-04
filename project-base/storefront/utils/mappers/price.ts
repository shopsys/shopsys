export const mapPriceForCalculations = (price: string): number => parseFloat(price);

const roundPrice = (price: number): number => Math.round((price + Number.EPSILON) * 100) / 100;

export const getPriceRounded = (price: string): number => roundPrice(mapPriceForCalculations(price));
