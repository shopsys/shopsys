import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonPageBrandsOverview } from 'components/Blocks/Skeleton/SkeletonPageBrandsOverview';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrandsQuery } from 'graphql/requests/brands/queries/BrandsQuery.generated';
import useTranslation from 'next-translate/useTranslation';

export const BrandsContent: FC = () => {
    const { t } = useTranslation();
    const [{ data: brandsData, fetching: areBrandsFetching }] = useBrandsQuery();

    if (areBrandsFetching) {
        return (
            <Webline>
                <SkeletonPageBrandsOverview />
            </Webline>
        );
    }

    if (!brandsData) {
        return null;
    }

    return <SimpleNavigation isWithoutSlider listedItems={brandsData.brands} title={t('Brands')} />;
};
