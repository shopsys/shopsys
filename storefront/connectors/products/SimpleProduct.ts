import { getFirstImageSize } from 'connectors/image/Image';
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
        image: getFirstImageSize(simpleProductApiData.images),
        unitName: simpleProductApiData.unit.name,
    };
};
