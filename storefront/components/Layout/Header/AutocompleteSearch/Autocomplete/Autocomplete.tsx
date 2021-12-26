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

const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchType | undefined;
    isAutocompleteActive: boolean;
    autocompleteSearchQueryValue: string;
};

const Autocomplete: FC<AutocompleteProps> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainConfig.url);

    return (
        <AutocompleteStyled isActive={props.isAutocompleteActive}>
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
                                    <ProductsSearchResultStyled>
                                        {props.autocompleteSearchResults.productsSearch.products.map(
                                            (product, index) =>
                                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                                    <ProductSearchResultItemStyled key={product.slug}>
                                                        <NextLink href={product.slug}>
                                                            <ProductSearchResultLinkStyled>
                                                                <ProductSearchResultImageWrapperStyled>
                                                                    <Image image={product.image} alt={product.name} />
                                                                </ProductSearchResultImageWrapperStyled>
                                                                <ProductSearchResultNameStyled>
                                                                    {product.name}
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
                                    <SearchResultGroupStyled>
                                        {props.autocompleteSearchResults.brandSearch.map(
                                            (brand, index) =>
                                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                                    <li key={brand.slug}>
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
                                    <SearchResultGroupStyled>
                                        {props.autocompleteSearchResults.categoriesSearch.categories.map(
                                            (category, index) =>
                                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                                    <li key={category.slug}>
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
                                    <SearchResultGroupStyled>
                                        {props.autocompleteSearchResults.articlesSearch.map(
                                            (article, index) =>
                                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                                    <li key={article.slug}>
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
