import { SimpleProductFragmentApi } from 'graphql/generated';
import { SimpleProductType } from 'types/product';

export const mapSimpleProductApiData = (simpleProductApiData: SimpleProductFragmentApi): SimpleProductType => {
    return {
        ...simpleProductApiData,
        unitName: simpleProductApiData.unit.name,
        categoryNames: simpleProductApiData.categories.map((category) => category.name),
    };
};
