import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { SkeletonPageSearch } from 'components/Blocks/Skeleton/SkeletonPageSearch';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { TIDs } from 'cypress/tids';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isClient } from 'utils/isClient';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { useCurrentSearchStringQuery } from 'utils/queryParams/useCurrentSearchStringQuery';
import { SearchProducts } from './SearchProducts';
import { type useSearchQuery } from './searchUtils';

type SearchPageContentProps = {
    searchData: ReturnType<typeof useSearchQuery>['searchData'];
    isSearchFetching: boolean;
};

export const SearchPageContent: FC<SearchPageContentProps> = ({ searchData, isSearchFetching }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const searchString = useCurrentSearchStringQuery();

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

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
