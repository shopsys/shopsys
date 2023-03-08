export const mapPriceForCalculations = (price: string): number => parseFloat(price);

export const roundPrice = (price: number): number => Math.round((price + Number.EPSILON) * 100) / 100;
