import { TIDs } from 'cypress/tids';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getQueriesForVisibleUrl } from 'utils/queryParams/pushQueries';
import { useGetSortTarget } from 'utils/queryParams/useUpdateSortQuery';
import { type SortOptionsLabels } from './SortingBar';
import { SortingBarItem, SortingBarItemVariant } from './SortingBarItem';

type SortingBarOptionsProps = {
    variant: SortingBarItemVariant;
    selectedSortOption: TypeProductOrderingModeEnum;
    sortOptions: TypeProductOrderingModeEnum[];
    sortOptionsLabels: SortOptionsLabels;
    onChangeSort: (sortOption: TypeProductOrderingModeEnum) => void;
};

export const SortingBarOptions: FC<SortingBarOptionsProps> = ({
    variant,
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    onChangeSort,
}) => {
    const { t } = useTranslation();
    const router = useRouter();
    const getSortTarget = useGetSortTarget();
    // `asPath` leaves out the locale prefix of a domain such as /sk, which a native link needs.
    const baseUrlWithLocale = getBaseUrlWithLocale('', router.locale);
    const currentPathname = router.asPath.split('?')[0];

    // A link opened in a new tab leads to the same URL as the sort change itself, including the redirect from an SEO
    // category, so it starts on the first page and leaves out the default sort.
    const getSortHref = (sortOption: TypeProductOrderingModeEnum) => {
        const { query: sortQuery, pathnameOverride } = getSortTarget(sortOption);
        const sortLinkPathname = `${baseUrlWithLocale}${pathnameOverride ?? currentPathname}`;
        const sortParams = new URLSearchParams(getQueriesForVisibleUrl(sortQuery) as Record<string, string>).toString();

        return sortParams ? `${sortLinkPathname}?${sortParams}` : sortLinkPathname;
    };

    const renderSortOption = (sortOption: TypeProductOrderingModeEnum) => {
        const isSelectedSortOption = sortOption === selectedSortOption;
        const sortOptionLabel = sortOptionsLabels[sortOption];
        const sortOptionAriaLabel = isSelectedSortOption
            ? t('Sorted by {{ sortOption }}', { ns: 'accessibility', sortOption: sortOptionLabel })
            : t('Sort by {{ sortOption }}', { ns: 'accessibility', sortOption: sortOptionLabel });

        return (
            <SortingBarItem
                key={sortOption}
                href={getSortHref(sortOption)}
                isActive={isSelectedSortOption}
                tid={`${TIDs.blocks_sortingbar_option_}${sortOption}`}
                variant={variant}
                ariaLabel={sortOptionAriaLabel}
                onClick={() => onChangeSort(sortOption)}
            >
                {sortOptionLabel}
            </SortingBarItem>
        );
    };

    return <>{sortOptions.map(renderSortOption)}</>;
};
