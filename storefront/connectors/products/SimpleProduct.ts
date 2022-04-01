import { getFirstImage } from 'connectors/image/Image';
import { mapAvailabilityData } from 'connectors/availability/Availability';
import { mapProductPriceData } from 'connectors/price/Prices';
import { SimpleProductFragmentApi } from 'graphql/generated';
import { SimpleProductType } from 'types/product';

export const mapSimpleProductApiData = (
    simpleProductApiData: SimpleProductFragmentApi,
    currencyCode: string,
): SimpleProductType => {
    return {
        ...simpleProductApiData,
        price: mapProductPriceData(simpleProductApiData.price, currencyCode),
        image: getFirstImage(simpleProductApiData.images),
        unitName: simpleProductApiData.unit.name,
        categoryNames: simpleProductApiData.categories.map((category) => category.name),
        availability: mapAvailabilityData(simpleProductApiData.availability),
    };
};
