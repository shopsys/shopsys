import { FC } from 'react';
import { getBrands } from 'connectors/brands/Brands';
import SimpleNavigation from 'components/Blocks/SimpleNavigation';
import Webline from 'components/Layout/Webline';

const Brands: FC = () => {
    const brands = getBrands();

    if (brands === undefined) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation listedItems={brands} />
        </Webline>
    );
};

export default Brands;
