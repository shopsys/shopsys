import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrandsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const BrandsContent: FC = () => {
    const [{ data: brandsData }] = useQueryError(useBrandsQueryApi());

    if (brandsData === undefined) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation listedItems={brandsData.brands} />
        </Webline>
    );
};
