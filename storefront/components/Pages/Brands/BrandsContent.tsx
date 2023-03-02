import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrands } from 'connectors/brands/Brands';

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
