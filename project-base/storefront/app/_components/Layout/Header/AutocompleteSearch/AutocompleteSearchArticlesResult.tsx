import { AutocompleteSearchResultSection } from './AutocompleteSearchResultSection';
import { AUTOCOMPLETE_ARTICLE_LIMIT } from './constants';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Tag } from 'components/Basic/Tag/Tag';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';

type AutocompleteSearchArticlesResultProps = {
    articlesSearch: TypeAutocompleteSearchQuery['articlesSearch'];
    // autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchArticlesResult: FC<AutocompleteSearchArticlesResultProps> = async ({
    articlesSearch,
    // autocompleteSearchQueryValue,
}) => {
    const t = await getTranslation();

    if (!articlesSearch.length) {
        return null;
    }

    const title = `${t('Articles')} (${articlesSearch.length})`;

    return (
        <AutocompleteSearchResultSection title={title}>
            {articlesSearch.slice(0, AUTOCOMPLETE_ARTICLE_LIMIT).map((article) => (
                <li key={article.slug}>
                    <Tag
                        href={article.slug}
                        type={article.__typename === 'ArticleSite' ? 'article' : 'blogArticle'}
                        // onClick={() => {
                        // onGtmAutocompleteResultClickEventHandler(
                        //     autocompleteSearchQueryValue,
                        //     GtmSectionType.article,
                        //     article.name,
                        // );
                        // }}
                    >
                        {article.name}
                    </Tag>
                </li>
            ))}
        </AutocompleteSearchResultSection>
    );
};
