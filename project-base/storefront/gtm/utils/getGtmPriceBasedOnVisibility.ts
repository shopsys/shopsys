import { isPriceVisible } from 'utils/mappers/price';

export const getGtmPriceBasedOnVisibility = (priceAsString: string) =>
    isPriceVisible(priceAsString) ? parseFloat(priceAsString) : null;
