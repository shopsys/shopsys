import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrandsQueryApi } from 'graphql/requests/brands/queries/BrandsQuery.generated';
export const BrandsContent: FC = () => {
    const [{ data: brandsData }] = useBrandsQueryApi();

    if (brandsData === undefined) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation listedItems={brandsData.brands} />
        </Webline>
    );
};
