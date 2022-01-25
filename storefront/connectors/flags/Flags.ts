import { FlagDetailFragmentApi } from 'graphql/generated';
import { FlagDetailType } from 'types/flag';
import { mapListedProductConnectionType } from 'connectors/products/Products';

export const mapFlagDetailApiData = (apiData: FlagDetailFragmentApi, currencyCode: string): FlagDetailType => {
    return {
        ...apiData,
        __typename: 'Flag',
        productConnection: mapListedProductConnectionType(apiData.products, currencyCode),
    };
};
