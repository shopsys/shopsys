import { FC } from 'react';
import SimpleNavigation from 'components/Blocks/SimpleNavigation';
import { useBrands } from 'connectors/brands/Brands';
import Webline from 'components/Layout/Webline';

const Brands: FC = () => {
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

export default Brands;
