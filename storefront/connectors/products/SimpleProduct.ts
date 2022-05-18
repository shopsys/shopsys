import { getFirstImage } from 'connectors/image/Image';
import { mapProductPriceData } from 'connectors/price/Prices';
import { SimpleProductConnectionFragmentApi, SimpleProductFragmentApi } from 'graphql/generated';
import { SimpleProductConnectionType, SimpleProductType } from 'types/product';

export const mapSimpleProductApiData = (
    simpleProductApiData: SimpleProductFragmentApi,
    currencyCode: string,
): SimpleProductType => {
    return {
        ...simpleProductApiData,
        price: mapProductPriceData(simpleProductApiData.price, currencyCode),
        image: getFirstImage(simpleProductApiData.images),
        unitName: simpleProductApiData.unit.name,
    };
};

export const mapSimpleProductConnectionApiData = (
    apiData: SimpleProductConnectionFragmentApi,
    currencyCode: string,
): SimpleProductConnectionType => {
    const mappedProducts = [];

    if (apiData.edges !== null) {
        for (const productEdge of apiData.edges) {
            if (productEdge?.node !== undefined && productEdge.node !== null) {
                mappedProducts.push(mapSimpleProductApiData(productEdge.node, currencyCode));
            }
        }
    }

    return { totalCount: apiData.totalCount, products: mappedProducts };
};
