export const mapPriceForCalculations = (price: string): number => Number.parseFloat(price);

export const roundPrice = (price: number): number => Math.round((price + Number.EPSILON) * 100) / 100;
