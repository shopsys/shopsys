import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { SearchResultLink, SearchResultSectionGroup, SearchResultSectionTitle } from './AutocompleteSearchPopup';
import { AUTOCOMPLETE_ARTICLE_LIMIT } from './constants';

type AutocompleteSearchArticlesResultProps = {
    articlesSearch: TypeAutocompleteSearchQuery['articlesSearch'];
    onClosePopupCallback: () => void;
    autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchArticlesResult: FC<AutocompleteSearchArticlesResultProps> = ({
    autocompleteSearchQueryValue,
    articlesSearch,
    onClosePopupCallback,
}) => {
    const { t } = useTranslation();

    if (!articlesSearch.length) {
        return null;
    }

    return (
        <div>
            <SearchResultSectionTitle>
                {t('Articles')}
                {` (${articlesSearch.length})`}
            </SearchResultSectionTitle>

            <SearchResultSectionGroup>
                {articlesSearch.slice(0, AUTOCOMPLETE_ARTICLE_LIMIT).map((article) => (
                    <li key={article.slug}>
                        <SearchResultLink
                            href={article.slug}
                            type={article.__typename === 'ArticleSite' ? 'article' : 'blogArticle'}
                            onClick={() => {
                                onGtmAutocompleteResultClickEventHandler(
                                    autocompleteSearchQueryValue,
                                    GtmSectionType.article,
                                    article.name,
                                );
                                onClosePopupCallback();
                            }}
                        >
                            {article.name}
                        </SearchResultLink>
                    </li>
                ))}
            </SearchResultSectionGroup>
        </div>
    );
};
