import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import {
    AutocompleteSearchQueryApi,
    ListedProductFragmentApi,
    SimpleCategoryFragmentApi,
    SimpleProductFragmentApi,
} from 'graphql/generated';
import { onGtmAutocompleteResultClickEventHandler, onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { forwardRef, useMemo } from 'react';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { GtmProductListNameType, GtmSectionType } from 'gtm/types/enums';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchQueryApi;
    autocompleteSearchQueryValue: string;
    onClickLink: () => void;
};

const TEST_IDENTIFIER = 'layout-header-search-autocomplete-popup';

const imageTwClass = 'lg:w-full';

export const AutocompleteSearchPopup: FC<AutocompleteProps> = ({
    autocompleteSearchQueryValue,
    autocompleteSearchResults: { articlesSearch, brandSearch, categoriesSearch, productsSearch },
    onClickLink,
}) => {
    const router = useRouter();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const mappedProductSearchResults = useMemo(
        () => mapConnectionEdges<ListedProductFragmentApi>(productsSearch.edges),
        [productsSearch.edges],
    );
    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(categoriesSearch.edges),
        [categoriesSearch.edges],
    );

    const isWithResults =
        articlesSearch.length || brandSearch.length || categoriesSearch.totalCount || productsSearch.totalCount;

    const onProductDetailRedirectHandler =
        (
            product: SimpleProductFragmentApi | ListedProductFragmentApi,
            gtmProductListName: GtmProductListNameType,
            index: number,
        ) =>
        () => {
            onGtmProductClickEventHandler(product, gtmProductListName, index, url);
            onGtmAutocompleteResultClickEventHandler(
                autocompleteSearchQueryValue,
                GtmSectionType.product,
                product.fullName,
            );
        };

    if (!isWithResults) {
        return (
            <div className="flex items-center">
                <Icon icon="warning" alt="warning" />
                <span className="flex-1 pl-4 text-sm">{t('Could not find any results for the given query.')}</span>
            </div>
        );
    }

    const handleClickLink = (callback: () => void) => () => {
        onClickLink();
        callback();
    };

    return (
        <>
            {productsSearch.totalCount > 0 && (
                <div>
                    <SearchResultSectionTitle>
                        {`${t('Products')} (${productsSearch.totalCount})`}
                    </SearchResultSectionTitle>

                    <ul
                        className="flex flex-col gap-4 lg:grid lg:grid-cols-5 lg:gap-2"
                        data-testid={TEST_IDENTIFIER + '-products'}
                    >
                        {mappedProductSearchResults?.map(
                            (product, index) =>
                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                    <li
                                        className="text-sm"
                                        key={product.slug}
                                        data-testid={TEST_IDENTIFIER + '-products-' + index}
                                    >
                                        <ExtendedNextLink
                                            href={product.slug}
                                            type="product"
                                            className="flex cursor-pointer items-center gap-2 text-dark no-underline outline-none lg:flex-col lg:items-start"
                                            onClick={handleClickLink(
                                                onProductDetailRedirectHandler(
                                                    product,
                                                    GtmProductListNameType.autocomplete_search_results,
                                                    index,
                                                ),
                                            )}
                                        >
                                            <Image
                                                className="flex h-16 w-20 items-center justify-center"
                                                wrapperClassName={imageTwClass}
                                                image={product.mainImage}
                                                type="thumbnailMedium"
                                                alt={product.mainImage?.name || product.fullName}
                                            />

                                            <span className="flex-1">{product.fullName}</span>

                                            <span className="font-bold text-primary">
                                                {formatPrice(product.price.priceWithVat)}
                                            </span>
                                        </ExtendedNextLink>
                                    </li>
                                ),
                        )}
                    </ul>
                </div>
            )}

            {brandSearch.length > 0 && (
                <div>
                    <SearchResultSectionTitle>{`${t('Brands')} (${brandSearch.length})`}</SearchResultSectionTitle>

                    <SearchResultSectionGroup dataTestId={TEST_IDENTIFIER + '-brands'}>
                        {brandSearch.map(
                            (brand, index) =>
                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                    <li key={brand.slug} data-testid={TEST_IDENTIFIER + '-brands-' + index}>
                                        <SearchResultLink
                                            href={brand.slug}
                                            type="brand"
                                            onClick={handleClickLink(() =>
                                                onGtmAutocompleteResultClickEventHandler(
                                                    autocompleteSearchQueryValue,
                                                    GtmSectionType.brand,
                                                    brand.name,
                                                ),
                                            )}
                                        >
                                            {brand.name}
                                        </SearchResultLink>
                                    </li>
                                ),
                        )}
                    </SearchResultSectionGroup>
                </div>
            )}

            {categoriesSearch.totalCount > 0 && (
                <div>
                    <SearchResultSectionTitle>
                        {`${t('Categories')} (${categoriesSearch.totalCount})`}
                    </SearchResultSectionTitle>

                    <SearchResultSectionGroup dataTestId={TEST_IDENTIFIER + '-categories'}>
                        {mappedCategoriesSearchResults?.map(
                            (category, index) =>
                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                    <li key={category.slug} data-testid={TEST_IDENTIFIER + '-categories-' + index}>
                                        <SearchResultLink
                                            href={category.slug}
                                            type="category"
                                            onClick={handleClickLink(() =>
                                                onGtmAutocompleteResultClickEventHandler(
                                                    autocompleteSearchQueryValue,
                                                    GtmSectionType.category,
                                                    category.name,
                                                ),
                                            )}
                                        >
                                            {category.name}
                                        </SearchResultLink>
                                    </li>
                                ),
                        )}
                    </SearchResultSectionGroup>
                </div>
            )}

            {articlesSearch.length > 0 && (
                <div>
                    <SearchResultSectionTitle>{`${t('Articles')} (${articlesSearch.length})`}</SearchResultSectionTitle>

                    <SearchResultSectionGroup dataTestId={TEST_IDENTIFIER + '-articles'}>
                        {articlesSearch.map(
                            (article, index) =>
                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                    <li key={article.slug} data-testid={TEST_IDENTIFIER + '-articles-' + index}>
                                        <SearchResultLink
                                            href={article.slug}
                                            type={article.__typename === 'ArticleSite' ? 'article' : 'blogArticle'}
                                            onClick={handleClickLink(() =>
                                                onGtmAutocompleteResultClickEventHandler(
                                                    autocompleteSearchQueryValue,
                                                    GtmSectionType.article,
                                                    article.name,
                                                ),
                                            )}
                                        >
                                            {article.name}
                                        </SearchResultLink>
                                    </li>
                                ),
                        )}
                    </SearchResultSectionGroup>
                </div>
            )}

            <div className="flex justify-center">
                <Button
                    size="small"
                    onClick={handleClickLink(() =>
                        router.push({
                            pathname: searchUrl,
                            query: { q: autocompleteSearchQueryValue },
                        }),
                    )}
                    dataTestId={TEST_IDENTIFIER + '-all-button'}
                >
                    {t('View all results')}
                </Button>
            </div>
        </>
    );
};

const SearchResultSectionTitle: FC = ({ children }) => {
    return <p className="mb-6 text-sm text-greyLight">{children}</p>;
};

const SearchResultSectionGroup: FC = ({ children, dataTestId }) => (
    <ul className="flex flex-col gap-2" data-testid={dataTestId}>
        {children}
    </ul>
);

const SearchResultLink: FC<{ onClick: () => void; href: string; type: FriendlyPagesTypesKeys }> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, onClick, href, type }, _) => (
        <ExtendedNextLink
            className="text-sm font-bold text-dark no-underline"
            onClick={onClick}
            href={href}
            type={type}
        >
            {children}
        </ExtendedNextLink>
    ),
);

SearchResultLink.displayName = 'SearchResultLink';
