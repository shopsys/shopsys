import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { IconImage } from 'components/Basic/Icon/IconImage';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { SimpleCategoryFragment } from 'graphql/requests/categories/fragments/SimpleCategoryFragment.generated';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { AutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { onGtmAutocompleteResultClickEventHandler, onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmProductListNameType, GtmSectionType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { forwardRef, useMemo } from 'react';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchQuery;
    autocompleteSearchQueryValue: string;
    onClickLink: () => void;
};

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
        () => mapConnectionEdges<ListedProductFragment>(productsSearch.edges),
        [productsSearch.edges],
    );
    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragment>(categoriesSearch.edges),
        [categoriesSearch.edges],
    );

    const isWithResults =
        articlesSearch.length || brandSearch.length || categoriesSearch.totalCount || productsSearch.totalCount;

    const onProductDetailRedirectHandler =
        (
            product: SimpleProductFragment | ListedProductFragment,
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
                <IconImage alt="warning" icon="warning" />
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
                        tid={TIDs.layout_header_search_autocomplete_popup_products}
                    >
                        {mappedProductSearchResults?.map(
                            (product, index) =>
                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                    <li key={product.slug} className="text-sm">
                                        <ExtendedNextLink
                                            className="flex cursor-pointer items-center gap-2 text-dark no-underline outline-none lg:flex-col lg:items-start"
                                            href={product.slug}
                                            type="product"
                                            onClick={handleClickLink(
                                                onProductDetailRedirectHandler(
                                                    product,
                                                    GtmProductListNameType.autocomplete_search_results,
                                                    index,
                                                ),
                                            )}
                                        >
                                            <div className="relative mx-auto flex h-20 w-20 items-center justify-center lg:w-full">
                                                <Image
                                                    fill
                                                    alt={product.mainImage?.name || product.fullName}
                                                    className="object-contain"
                                                    sizes="(max-width: 768px) 80px, (max-width: 1024px) 20vw, 110px"
                                                    src={product.mainImage?.url}
                                                />
                                            </div>

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

                    <SearchResultSectionGroup>
                        {brandSearch.map(
                            (brand, index) =>
                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                    <li key={brand.slug}>
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

                    <SearchResultSectionGroup>
                        {mappedCategoriesSearchResults?.map(
                            (category, index) =>
                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                    <li key={category.slug}>
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

                    <SearchResultSectionGroup>
                        {articlesSearch.map(
                            (article, index) =>
                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                    <li key={article.slug}>
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

const SearchResultSectionGroup: FC = ({ children }) => <ul className="flex flex-col gap-2">{children}</ul>;

const SearchResultLink: FC<{ onClick: () => void; href: string; type: FriendlyPagesTypesKey }> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, onClick, href, type }, _) => (
        <ExtendedNextLink
            className="text-sm font-bold text-dark no-underline"
            href={href}
            type={type}
            onClick={onClick}
        >
            {children}
        </ExtendedNextLink>
    ),
);

SearchResultLink.displayName = 'SearchResultLink';
