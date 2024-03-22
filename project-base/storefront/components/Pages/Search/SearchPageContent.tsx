import { SearchProducts } from './SearchProducts';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonPageSearch } from 'components/Blocks/Skeleton/SkeletonPageSearch';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { useSearchQueryApi } from 'graphql/generated';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';

export const SearchPageContent: FC = () => {
    const { t } = useTranslation();
    const { searchString } = useQueryParams();
    const userIdentifier = usePersistStore((state) => state.userId)!;

    const [{ data: searchData, fetching }] = useSearchQueryApi({
        variables: {
            search: searchString!,
            isAutocomplete: false,
            userIdentifier,
        },
        pause: !searchString,
    });

    if (!searchString) {
        return (
            <div className="mb-5 p-12 text-center">
                <strong>{t('There are no results as you have searched with an empty query...')}</strong>
            </div>
        );
    }

    return (
        <>
            <Webline>
                {fetching && <SkeletonPageSearch />}

                {!!searchData && !fetching && <SearchContent searchResults={searchData} />}

                <SearchProducts />
            </Webline>

            <LastVisitedProducts />
        </>
    );
};
