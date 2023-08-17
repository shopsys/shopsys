import { getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextRouter, useRouter } from 'next/router';
import { useState, useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useClient, Client } from 'urql';
import { CategoryDetailFragmentApi } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import {
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.generated';
import { Maybe, ProductFilterApi, ProductOrderingModeEnumApi } from 'graphql/requests/types';

export const useCategoryDetailData = (
    filter: ProductFilterApi | null,
): [undefined | CategoryDetailFragmentApi, boolean] => {
    const client = useClient();
    const router = useRouter();
    const urlSlug = getSlugFromUrl(router.asPath);
    const { sort } = useQueryParams();
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState<undefined | CategoryDetailFragmentApi>(
        readCategoryDetailFromCache(client, urlSlug, sort, filter),
    );
    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);

    const [fetching, setFetching] = useState<boolean>(false);

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            return;
        }
        setFetching(true);

        client
            .query<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
                urlSlug,
                orderingMode: sort ?? null,
                filter,
            })
            .toPromise()
            .then((response) => {
                setCategoryDetailData(response.data?.category ?? undefined);
                handleSeoCategorySlugUpdate(
                    router,
                    urlSlug,
                    response.data?.category?.originalCategorySlug,
                    response.data?.category?.slug,
                    setWasRedirectedToSeoCategory,
                    setOriginalCategorySlug,
                );
            })
            .finally(() => setFetching(false));
    }, [urlSlug, sort, JSON.stringify(filter)]);

    return [categoryDetailData, fetching];
};

const handleSeoCategorySlugUpdate = (
    router: NextRouter,
    urlSlug: string,
    originalCategorySlug: string | undefined | null,
    categorySlug: string | undefined,
    setWasRedirectedToSeoCategory: (value: boolean) => void,
    setOriginalCategorySlug: (value: string | undefined) => void,
) => {
    const isCurrentAndRedirectSlugDifferent = getStringWithoutLeadingSlash(categorySlug ?? '') !== urlSlug;

    if (originalCategorySlug && isCurrentAndRedirectSlugDifferent && categorySlug) {
        setWasRedirectedToSeoCategory(true);
        router.replace(
            { pathname: '/categories/[categorySlug]', query: { categorySlug } },
            { pathname: categorySlug },
            {
                shallow: true,
            },
        );
    }

    setOriginalCategorySlug(originalCategorySlug ?? undefined);
};

const readCategoryDetailFromCache = (
    client: Client,
    urlSlug: string,
    orderingMode: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
) =>
    client.readQuery<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
        urlSlug,
        orderingMode,
        filter,
    })?.data?.category ?? undefined;
