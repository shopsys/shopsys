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
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { forwardRef, useCallback, useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { GtmProductListNameType, GtmSectionType } from 'gtm/types/enums';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchQueryApi | undefined;
    isAutocompleteActive: boolean;
    autocompleteSearchQueryValue: string;
};

const TEST_IDENTIFIER = 'layout-header-search-autocomplete';

export const Autocomplete: FC<AutocompleteProps> = ({
    autocompleteSearchQueryValue,
    autocompleteSearchResults,
    isAutocompleteActive,
}) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const mappedProductSearchResults = useMemo(
        () => mapConnectionEdges<ListedProductFragmentApi>(autocompleteSearchResults?.productsSearch.edges),
        [autocompleteSearchResults?.productsSearch.edges],
    );
    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(autocompleteSearchResults?.categoriesSearch.edges),
        [autocompleteSearchResults?.categoriesSearch.edges],
    );

    const onProductDetailRedirectHandler = useCallback(
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
            },
        [autocompleteSearchQueryValue, url],
    );

    return (
        <div
            className={twJoin(
                'absolute top-0 left-0 z-aboveMenu w-full bg-creamWhite px-7 pb-6 shadow-md max-vl:origin-top max-vl:scale-y-90 max-vl:opacity-0 max-vl:transition lg:relative lg:left-0 lg:right-auto lg:w-full lg:origin-top lg:scale-y-90 lg:rounded lg:p-8 lg:transition-all vl:w-[576px]',
                isAutocompleteActive
                    ? 'pointer-events-auto pt-32 opacity-100 max-vl:scale-y-100 max-vl:opacity-100 vl:top-3'
                    : 'pointer-events-none lg:opacity-0',
            )}
            data-testid={TEST_IDENTIFIER}
        >
            {(() => {
                if (autocompleteSearchResults === undefined) {
                    return null;
                }

                if (areAllResultsEmpty(autocompleteSearchResults)) {
                    return (
                        <div className="flex items-center">
                            <Icon iconType="image" icon="warning" alt="warning" />
                            <span className="flex-1 pl-4 text-sm">
                                {t('Could not find any results for the given query.')}
                            </span>
                        </div>
                    );
                }

                return (
                    <>
                        {autocompleteSearchResults.productsSearch.totalCount > 0 && (
                            <>
                                <p className="text-sm text-greyLight">
                                    {`${t('Products')} (${autocompleteSearchResults.productsSearch.totalCount})`}
                                </p>
                                <ul
                                    className="mb-3 -ml-4 flex list-none flex-wrap"
                                    data-testid={TEST_IDENTIFIER + '-products'}
                                >
                                    {mappedProductSearchResults?.map(
                                        (product, index) =>
                                            index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                                <li
                                                    className="mb-2 w-full pl-4 text-sm lg:mb-4 lg:w-1/5"
                                                    key={product.slug}
                                                    data-testid={TEST_IDENTIFIER + '-products-' + index}
                                                >
                                                    <ExtendedNextLink
                                                        href={product.slug}
                                                        type="product"
                                                        className="flex cursor-pointer items-center text-dark no-underline outline-none lg:flex-col lg:items-start"
                                                        onClick={onProductDetailRedirectHandler(
                                                            product,
                                                            GtmProductListNameType.autocomplete_search_results,
                                                            index,
                                                        )}
                                                    >
                                                        <>
                                                            <div className="relative mr-2 h-16 w-20">
                                                                <Image
                                                                    className="w-full"
                                                                    image={product.mainImage}
                                                                    type="thumbnailMedium"
                                                                    alt={product.mainImage?.name || product.fullName}
                                                                />
                                                            </div>
                                                            <span className="mb-1 flex-1">{product.fullName}</span>

                                                            <span className="font-bold text-primary">
                                                                {formatPrice(product.price.priceWithVat)}
                                                            </span>
                                                        </>
                                                    </ExtendedNextLink>
                                                </li>
                                            ),
                                    )}
                                </ul>
                            </>
                        )}
                        {autocompleteSearchResults.brandSearch.length > 0 && (
                            <>
                                <p className="text-sm text-greyLight">
                                    {`${t('Brands')} (${autocompleteSearchResults.brandSearch.length})`}
                                </p>
                                <SearchResultGroup dataTestId={TEST_IDENTIFIER + '-brands'}>
                                    {autocompleteSearchResults.brandSearch.map(
                                        (brand, index) =>
                                            index < AUTOCOMPLETE_BRAND_LIMIT && (
                                                <li key={brand.slug} data-testid={TEST_IDENTIFIER + '-brands-' + index}>
                                                    <SearchResultLink
                                                        href={brand.slug}
                                                        type="brand"
                                                        onClick={() =>
                                                            onGtmAutocompleteResultClickEventHandler(
                                                                autocompleteSearchQueryValue,
                                                                GtmSectionType.brand,
                                                                brand.name,
                                                            )
                                                        }
                                                    >
                                                        {brand.name}
                                                    </SearchResultLink>
                                                </li>
                                            ),
                                    )}
                                </SearchResultGroup>
                            </>
                        )}
                        {autocompleteSearchResults.categoriesSearch.totalCount > 0 && (
                            <>
                                <p className="text-sm text-greyLight">
                                    {`${t('Categories')} (${autocompleteSearchResults.categoriesSearch.totalCount})`}
                                </p>
                                <SearchResultGroup dataTestId={TEST_IDENTIFIER + '-categories'}>
                                    {mappedCategoriesSearchResults?.map(
                                        (category, index) =>
                                            index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                                <li
                                                    key={category.slug}
                                                    data-testid={TEST_IDENTIFIER + '-categories-' + index}
                                                >
                                                    <SearchResultLink
                                                        href={category.slug}
                                                        type="category"
                                                        onClick={() =>
                                                            onGtmAutocompleteResultClickEventHandler(
                                                                autocompleteSearchQueryValue,
                                                                GtmSectionType.category,
                                                                category.name,
                                                            )
                                                        }
                                                    >
                                                        {category.name}
                                                    </SearchResultLink>
                                                </li>
                                            ),
                                    )}
                                </SearchResultGroup>
                            </>
                        )}
                        {autocompleteSearchResults.articlesSearch.length > 0 && (
                            <>
                                <p className="text-sm text-greyLight">
                                    {`${t('Articles')} (${autocompleteSearchResults.articlesSearch.length})`}
                                </p>
                                <SearchResultGroup dataTestId={TEST_IDENTIFIER + '-articles'}>
                                    {autocompleteSearchResults.articlesSearch.map(
                                        (article, index) =>
                                            index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                                <li
                                                    key={article.slug}
                                                    data-testid={TEST_IDENTIFIER + '-articles-' + index}
                                                >
                                                    <SearchResultLink
                                                        href={article.slug}
                                                        type={
                                                            article.__typename === 'ArticleSite'
                                                                ? 'article'
                                                                : 'blogArticle'
                                                        }
                                                        onClick={() =>
                                                            onGtmAutocompleteResultClickEventHandler(
                                                                autocompleteSearchQueryValue,
                                                                GtmSectionType.article,
                                                                article.name,
                                                            )
                                                        }
                                                    >
                                                        {article.name}
                                                    </SearchResultLink>
                                                </li>
                                            ),
                                    )}
                                </SearchResultGroup>
                            </>
                        )}
                        <div className="flex justify-center">
                            <Button
                                size="small"
                                onClick={() =>
                                    router.push({
                                        pathname: searchUrl,
                                        query: { q: autocompleteSearchQueryValue },
                                    })
                                }
                                dataTestId={TEST_IDENTIFIER + '-all-button'}
                            >
                                {t('View all results')}
                            </Button>
                        </div>
                    </>
                );
            })()}
        </div>
    );
};

const areAllResultsEmpty = (autocompleteSearchResults: AutocompleteSearchQueryApi | undefined) => {
    if (autocompleteSearchResults === undefined) {
        return false;
    }

    return (
        autocompleteSearchResults.articlesSearch.length === 0 &&
        autocompleteSearchResults.brandSearch.length === 0 &&
        autocompleteSearchResults.categoriesSearch.totalCount === 0 &&
        autocompleteSearchResults.productsSearch.totalCount === 0
    );
};

const SearchResultGroup: FC = ({ children, dataTestId }) => (
    <ul className="mb-3 list-none p-0" data-testid={dataTestId}>
        {children}
    </ul>
);

const SearchResultLink: FC<{ onClick: () => void; href: string; type: FriendlyPagesTypesKeys }> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, onClick, href, type }, _) => (
        <ExtendedNextLink
            className="flex w-full items-center py-3 text-sm font-bold text-dark no-underline"
            onClick={onClick}
            href={href}
            type={type}
        >
            {children}
        </ExtendedNextLink>
    ),
);

SearchResultLink.displayName = 'SearchResultLink';
