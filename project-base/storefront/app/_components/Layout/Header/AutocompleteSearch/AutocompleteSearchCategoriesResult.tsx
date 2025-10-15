import { AutocompleteSearchResultSection } from './AutocompleteSearchResultSection';
import { AUTOCOMPLETE_CATEGORY_LIMIT } from './constants';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Tag } from 'components/Basic/Tag/Tag';
import { TypeSimpleCategoryFragment } from 'graphql/requests/categories/fragments/SimpleCategoryFragment.ssr';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.ssr';
import { mapConnectionEdges } from 'utils/mappers/connection';

type AutocompleteSearchCategoriesResultProps = {
    categoriesSearch: TypeAutocompleteSearchQuery['categoriesSearch'];
    // autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchCategoriesResult: FC<AutocompleteSearchCategoriesResultProps> = async ({
    categoriesSearch,
    // autocompleteSearchQueryValue,
}) => {
    const t = await getTranslation();

    const mappedCategoriesSearchResults = mapConnectionEdges<TypeSimpleCategoryFragment>(categoriesSearch.edges);

    if (!mappedCategoriesSearchResults?.length) {
        return null;
    }

    const title = `${t('Categories')} (${mappedCategoriesSearchResults.length})`;

    return (
        <AutocompleteSearchResultSection title={title}>
            {mappedCategoriesSearchResults.slice(0, AUTOCOMPLETE_CATEGORY_LIMIT).map((category) => (
                <li key={category.slug}>
                    <Tag
                        href={category.slug}
                        type="category"
                        // onClick={() => {
                        // onGtmAutocompleteResultClickEventHandler(
                        //     autocompleteSearchQueryValue,
                        //     GtmSectionType.category,
                        //     category.name,
                        // );
                        // }}
                    >
                        {category.name}
                    </Tag>
                </li>
            ))}
        </AutocompleteSearchResultSection>
    );
};
