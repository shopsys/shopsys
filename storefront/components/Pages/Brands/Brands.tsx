import SimpleNavigation from 'components/Blocks/SimpleNavigation';
import Webline from 'components/Layout/Webline';
import { useBrands } from 'connectors/brands/Brands';
import { FC } from 'react';

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
