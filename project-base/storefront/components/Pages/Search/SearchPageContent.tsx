import { SearchProducts } from './SearchProducts';
import { useSearchQuery } from './searchUtils';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonPageSearch } from 'components/Blocks/Skeleton/SkeletonPageSearch';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { isClient } from 'utils/isClient';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { useCurrentSearchStringQuery } from 'utils/queryParams/useCurrentSearchStringQuery';

export const SearchPageContent: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const searchString = useCurrentSearchStringQuery();
    const { searchData, isSearchFetching } = useSearchQuery(searchString);

    const searchHeading = `${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`;

    if ((isSearchFetching || !isClient) && searchString) {
        return <SkeletonPageSearch />;
    }

    return (
        <VerticalStack gap="md">
            <Webline>
                <h1 data-tid={TIDs.search_results_heading}>{searchHeading}</h1>
            </Webline>

            {!searchString && (
                <div className="my-28 flex items-center justify-center">
                    <InfoIcon className="mr-4 w-8" />
                    <div className="h3">{t('There are no results as you have searched with an empty query...')}</div>
                </div>
            )}

            {!!searchData && !isSearchFetching && searchString && <SearchContent searchResults={searchData} />}

            {!isSearchFetching && isClient && searchString && <SearchProducts />}

            <LastVisitedProducts />
        </VerticalStack>
    );
};
