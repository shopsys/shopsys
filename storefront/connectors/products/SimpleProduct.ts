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
        unitName: simpleProductApiData.unit.name,
        categoryNames: simpleProductApiData.categories.map((category) => category.name),
    };
};
