import { SearchProducts } from './SearchProducts';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonPageSearch } from 'components/Blocks/Skeleton/SkeletonPageSearch';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { useSearchQuery } from 'graphql/requests/search/queries/SearchQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';
import { isClient } from 'utils/isClient';
import { useCurrentSearchStringQuery } from 'utils/queryParams/useCurrentSearchStringQuery';

export const SearchPageContent: FC = () => {
    const { t } = useTranslation();
    const searchString = useCurrentSearchStringQuery();
    const userIdentifier = usePersistStore((state) => state.userId);

    const [{ data: searchData, fetching }] = useSearchQuery({
        variables: {
            search: searchString!,
            isAutocomplete: false,
            userIdentifier: userIdentifier!,
        },
        pause: !searchString || !isClient || !userIdentifier,
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
                {(fetching || !isClient) && <SkeletonPageSearch />}

                {!!searchData && !fetching && <SearchContent searchResults={searchData} />}

                <SearchProducts />
            </Webline>

            <LastVisitedProducts />
        </>
    );
};
