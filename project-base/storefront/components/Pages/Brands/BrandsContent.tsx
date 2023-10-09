import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrandsQueryApi } from 'graphql/generated';

export const BrandsContent: FC = () => {
    const [{ data: brandsData }] = useBrandsQueryApi();

    if (!brandsData) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation isWithoutSlider linkType="brand" listedItems={brandsData.brands} />
        </Webline>
    );
};
