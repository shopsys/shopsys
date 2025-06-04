import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeSimpleCategoryFragment } from 'graphql/requests/categories/fragments/SimpleCategoryFragment.generated';
import { TypeSearchQuery } from 'graphql/requests/search/queries/SearchQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

type SearchContentProps = {
    searchResults: TypeSearchQuery;
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults }) => {
    const { t } = useTranslation();

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<TypeSimpleCategoryFragment>(searchResults.categoriesSearch.edges),
        [searchResults.categoriesSearch.edges],
    );

    return (
        <>
            {!!searchResults.articlesSearch.length && (
                <div>
                    <Webline>
                        <h2 className="h5 mb-2">{t('Found articles')}</h2>
                    </Webline>
                    <SimpleNavigation isWithoutSlider listedItems={searchResults.articlesSearch} />
                </div>
            )}

            {!!searchResults.brandSearch.length && (
                <div>
                    <Webline>
                        <h2 className="h5 mb-2">{t('Found brands')}</h2>
                    </Webline>
                    <SimpleNavigation isWithoutSlider listedItems={searchResults.brandSearch} />
                </div>
            )}

            {!!mappedCategoriesSearchResults?.length && (
                <div>
                    <Webline>
                        <h2 className="h5 mb-2">{t('Found categories')}</h2>
                    </Webline>
                    <SimpleNavigation isWithoutSlider listedItems={mappedCategoriesSearchResults} />
                </div>
            )}
        </>
    );
};
