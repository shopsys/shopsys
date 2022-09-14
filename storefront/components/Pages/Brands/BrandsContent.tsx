import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrands } from 'connectors/brands/Brands';
import { FC } from 'react';

export const BrandsContent: FC = () => {
    const brands = useBrands();

    if (brands === undefined) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation listedItems={brands} />
        </Webline>
    );
};
