import { TIDs } from 'cypress/tids';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { type SortOptionsLabels } from './SortingBar';
import { SortingBarItem } from './SortingBarItem';

type SortingBarOptionsProps = {
    itemRole: 'menuitem' | 'option';
    selectedSortOption: TypeProductOrderingModeEnum;
    sortOptions: TypeProductOrderingModeEnum[];
    sortOptionsLabels: SortOptionsLabels;
    onChangeSort: (sortOption: TypeProductOrderingModeEnum) => void;
};

export const SortingBarOptions: FC<SortingBarOptionsProps> = ({
    itemRole,
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    onChangeSort,
}) => {
    const { t } = useTranslation();
    const router = useRouter();
    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    return (
        <>
            {sortOptions.map((sortOption) => {
                const { page, ...queriesWithoutPage } = getUrlQueriesWithoutDynamicPageQueries(router.query);
                const sortParams = new URLSearchParams({
                    ...queriesWithoutPage,
                    sort: sortOption,
                }).toString();
                const sortHref = `${asPathWithoutQueryParams}?${sortParams}`;
                const isSelectedSortOption = sortOption === selectedSortOption;

                return (
                    <SortingBarItem
                        key={sortOption}
                        href={sortHref}
                        isActive={isSelectedSortOption}
                        role={itemRole}
                        tid={`${TIDs.blocks_sortingbar_option_}${sortOption}`}
                        ariaLabel={
                            isSelectedSortOption
                                ? t('Sorted by {{ sortOption }}', {
                                      ns: 'accessibility',
                                      sortOption: sortOptionsLabels[sortOption],
                                  })
                                : t('Sort by {{ sortOption }}', {
                                      ns: 'accessibility',
                                      sortOption: sortOptionsLabels[sortOption],
                                  })
                        }
                        onClick={() => onChangeSort(sortOption)}
                    >
                        {sortOptionsLabels[sortOption]}
                    </SortingBarItem>
                );
            })}
        </>
    );
};
