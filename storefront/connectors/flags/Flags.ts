import { mapListedProductConnectionType } from 'connectors/products/Products';
import { FlagDetailFragmentApi } from 'graphql/generated';
import { FlagDetailType } from 'types/flag';

export const mapFlagDetailApiData = (apiData: FlagDetailFragmentApi, currencyCode: string): FlagDetailType => {
    return {
        ...apiData,
        __typename: 'Flag',
        productConnection: mapListedProductConnectionType(apiData.products, currencyCode),
    };
};
