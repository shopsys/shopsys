import {
    AutocompleteBodyStyled,
    AutocompleteStyled,
    NoResultsMessageStyled,
    NoResultsMessageWrapperStyled,
    ProductSearchResultImageWrapperStyled,
    ProductSearchResultItemStyled,
    ProductSearchResultLinkStyled,
    ProductSearchResultNameStyled,
    ProductSearchResultPriceStyled,
    ProductsSearchResultStyled,
    SearchResultGroupStyled,
    SearchResultGroupTitleStyled,
    SearchResultLinkStyled,
    ShowAllResultsButtonWrapper,
} from './Autocomplete.style';
import { AutocompleteSearchType } from 'types/search';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Icon from 'components/Basic/Icon';
import Image from 'components/Basic/Image';
import NextLink from 'next/link';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchType | undefined;
    isAutocompleteActive: boolean;
    autocompleteSearchQueryValue: string;
};

const Autocomplete: FC<AutocompleteProps> = (props) => {
    const testIdentifier = 'layout-header-search-autocomplete';

    const router = useRouter();
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainConfig.url);

    return (
        <AutocompleteStyled isActive={props.isAutocompleteActive} data-testid={testIdentifier}>
            <AutocompleteBodyStyled isActive={props.isAutocompleteActive}>
                {(() => {
                    if (props.autocompleteSearchResults === undefined) {
                        return null;
                    }

                    if (areAllResultsEmpty(props.autocompleteSearchResults)) {
                        return (
                            <NoResultsMessageWrapperStyled>
                                <Icon iconType="image" icon="warning" alt="warning" />
                                <NoResultsMessageStyled>
                                    {t('Could not find any results for the given query.')}
                                </NoResultsMessageStyled>
                            </NoResultsMessageWrapperStyled>
                        );
                    }

                    return (
                        <>
                            {props.autocompleteSearchResults.productsSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Products')} (${
                                            props.autocompleteSearchResults.productsSearch.totalCount
                                        })`}
                                    </SearchResultGroupTitleStyled>
                                    <ProductsSearchResultStyled data-testid={testIdentifier + '-products'}>
                                        {props.autocompleteSearchResults.productsSearch.products.map(
                                            (product, index) =>
                                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                                    <ProductSearchResultItemStyled
                                                        key={product.slug}
                                                        data-testid={testIdentifier + '-products-' + index}
                                                    >
                                                        <NextLink href={product.slug}>
                                                            <ProductSearchResultLinkStyled>
                                                                <ProductSearchResultImageWrapperStyled>
                                                                    <Image
                                                                        image={product.image}
                                                                        alt={product.fullName}
                                                                    />
                                                                </ProductSearchResultImageWrapperStyled>
                                                                <ProductSearchResultNameStyled>
                                                                    {product.fullName}
                                                                </ProductSearchResultNameStyled>
                                                                <ProductSearchResultPriceStyled>
                                                                    {formatPrice(
                                                                        product.price.priceWithVat,
                                                                        domainConfig.currencyCode,
                                                                        t,
                                                                    )}
                                                                </ProductSearchResultPriceStyled>
                                                            </ProductSearchResultLinkStyled>
                                                        </NextLink>
                                                    </ProductSearchResultItemStyled>
                                                ),
                                        )}
                                    </ProductsSearchResultStyled>
                                </>
                            )}
                            {props.autocompleteSearchResults.brandSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Brands')} (${props.autocompleteSearchResults.brandSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-brands'}>
                                        {props.autocompleteSearchResults.brandSearch.map(
                                            (brand, index) =>
                                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                                    <li
                                                        key={brand.slug}
                                                        data-testid={testIdentifier + '-brands-' + index}
                                                    >
                                                        <NextLink href={brand.slug}>
                                                            <SearchResultLinkStyled>
                                                                {brand.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            {props.autocompleteSearchResults.categoriesSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Categories')} (${
                                            props.autocompleteSearchResults.categoriesSearch.totalCount
                                        })`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-categories'}>
                                        {props.autocompleteSearchResults.categoriesSearch.categories.map(
                                            (category, index) =>
                                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                                    <li
                                                        key={category.slug}
                                                        data-testid={testIdentifier + '-categories-' + index}
                                                    >
                                                        <NextLink href={category.slug}>
                                                            <SearchResultLinkStyled>
                                                                {category.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            {props.autocompleteSearchResults.articlesSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Articles')} (${props.autocompleteSearchResults.articlesSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-articles'}>
                                        {props.autocompleteSearchResults.articlesSearch.map(
                                            (article, index) =>
                                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                                    <li
                                                        key={article.slug}
                                                        data-testid={testIdentifier + '-articles-' + index}
                                                    >
                                                        <NextLink href={article.slug}>
                                                            <SearchResultLinkStyled>
                                                                {article.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            <ShowAllResultsButtonWrapper>
                                <Button
                                    type="button"
                                    size="small"
                                    onClick={() =>
                                        router.push({
                                            pathname: searchUrl,
                                            query: { q: props.autocompleteSearchQueryValue },
                                        })
                                    }
                                    data-testid={testIdentifier + '-all-button'}
                                >
                                    {t('View all results')}
                                </Button>
                            </ShowAllResultsButtonWrapper>
                        </>
                    );
                })()}
            </AutocompleteBodyStyled>
        </AutocompleteStyled>
    );
};

const areAllResultsEmpty = (autocompleteSearchResults: AutocompleteSearchType | undefined) => {
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

export default Autocomplete;
