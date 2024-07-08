import { AutocompleteSearchArticlesResult } from './AutocompleteSearchArticlesResult';
import { AutocompleteSearchBrandsResult } from './AutocompleteSearchBrandsResult';
import { AutocompleteSearchCategoriesResult } from './AutocompleteSearchCategoriesResult';
import { AutocompleteSearchProductsResult } from './AutocompleteSearchProductsResult';
import { AutocompleteSkeleton } from './AutocompleteSkeleton';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { IconImage } from 'components/Basic/IconImage/IconImage';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { m } from 'framer-motion';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { forwardRef } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { twJoin } from 'tailwind-merge';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { fadeAnimation } from 'utils/animations/animationVariants';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type AutocompleteProps = {
    autocompleteSearchResults: TypeAutocompleteSearchQuery | undefined;
    autocompleteSearchQueryValue: string;
    onClosePopupCallback: () => void;
    areAutocompleteSearchDataFetching: boolean;
};

export const AutocompleteSearchPopup: FC<AutocompleteProps> = ({
    autocompleteSearchQueryValue,
    areAutocompleteSearchDataFetching,
    autocompleteSearchResults,
    onClosePopupCallback,
}) => {
    const router = useRouter();
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const { articlesSearch, brandSearch, categoriesSearch, productsSearch } = autocompleteSearchResults || {};

    const isWithResults = !!(
        articlesSearch?.length ||
        brandSearch?.length ||
        categoriesSearch?.totalCount ||
        productsSearch?.totalCount
    );

    return (
        <RemoveScroll>
            <m.div
                animate="visible"
                exit="hidden"
                initial="hidden"
                variants={fadeAnimation}
                className={twJoin(
                    'gap-5 w-full absolute left-0 -bottom-3 z-aboveOverlay flex vl:w-[770px] origin-top translate-y-full flex-col vl:gap-6 rounded-xl bg-white p-7 overflow-auto',
                    'max-h-[calc(85vh-169px)] md:max-h-[calc(98vh-169px)] lg:max-h-[calc(98vh-180px)] vl:max-h-[calc(98vh-120px)]',
                )}
            >
                {areAutocompleteSearchDataFetching && <AutocompleteSkeleton />}
                {!areAutocompleteSearchDataFetching && !isWithResults && (
                    <div className="flex items-center">
                        <IconImage alt="warning" icon="warning" />
                        <span className="flex-1 pl-4 text-sm">
                            {t('Could not find any results for the given query.')}
                        </span>
                    </div>
                )}
                {!areAutocompleteSearchDataFetching && isWithResults && (
                    <>
                        {productsSearch && (
                            <AutocompleteSearchProductsResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                productsSearch={productsSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {brandSearch && (
                            <AutocompleteSearchBrandsResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                brandSearch={brandSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {categoriesSearch && (
                            <AutocompleteSearchCategoriesResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                categoriesSearch={categoriesSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {articlesSearch && (
                            <AutocompleteSearchArticlesResult
                                articlesSearch={articlesSearch}
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}

                        <div className="flex justify-center">
                            <Button
                                className="w-full md:w-fit"
                                variant="secondary"
                                onClick={() => {
                                    onClosePopupCallback();
                                    router.push({
                                        pathname: searchUrl,
                                        query: { q: autocompleteSearchQueryValue },
                                    });
                                }}
                            >
                                {t('View all results')}
                            </Button>
                        </div>
                    </>
                )}
            </m.div>
        </RemoveScroll>
    );
};

export const SearchResultSectionTitle: FC = ({ children }) => {
    return <p className="mb-2 text-lg">{children}</p>;
};

export const SearchResultSectionGroup: FC = ({ children }) => <ul className="flex flex-wrap gap-2">{children}</ul>;

export const SearchResultLink: FC<{ onClick: () => void; href: string; type: FriendlyPagesTypesKey }> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, onClick, href, type }, _) => (
        <ExtendedNextLink
            className="bg-skyBlue rounded-full px-3 py-2 text-white text-sm font-medium no-underline hover:no-underline block hover:text-white hover:bg-primaryLight"
            href={href}
            type={type}
            onClick={onClick}
        >
            {children}
        </ExtendedNextLink>
    ),
);

SearchResultLink.displayName = 'SearchResultLink';
