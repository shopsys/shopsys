import { enabledSortTypes, initialState, SortType, userActions } from 'redux/slices/user';
import { FC, useEffect } from 'react';
import { friendlyUrlQuery, getFriendlyUrlResolvedData, isProductType } from 'connectors/friendlyUrls/FriendlyUrls';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import CategoryDetailPage from 'components/Pages/CategoryDetail';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import CommonLayout from 'components/Layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import GetNewPagination from 'utils/GetNewPagination';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import ProductDetailPage from 'components/Pages/ProductDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import StoreDetailPage from 'components/Pages/StoreDetail';
import { StoreDetailType } from 'connectors/stores/types';
import { useRouter } from 'next/router';
import Webline from 'components/Layout/Webline';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const categoryDetailSortQuery = router.query.sort as string;
    const categoryDetailPageQuery = router.query.page as string;
    const categoryDetailSortState = useShopsysSelector((state) => state.user.sort);
    const categoryDetailSort = getCategoryDetailSort(
        typeof categoryDetailSortQuery !== 'undefined' ? categoryDetailSortQuery : categoryDetailSortState,
    );
    const updatedPagination = GetNewPagination(
        typeof categoryDetailPageQuery !== 'undefined'
            ? Number(categoryDetailPageQuery)
            : initialState.pagination.currentPage,
    );

    useEffect(() => {
        dispatch(userActions.setSort({ sort: categoryDetailSort as SortType }));
        dispatch(userActions.setPagination({ ...updatedPagination }));
    }, [categoryDetailSortQuery, categoryDetailPageQuery]);

    const data = getFriendlyUrlResolvedData(getUrlWithoutGetParameters(router.asPath));
    if (data === null || data === undefined) {
        return <DefaultErrorPage statusCode={404} />;
    }

    return (
        <CommonLayout>
            <Webline>
                <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />
            </Webline>
            {renderContent(data)}
        </CommonLayout>
    );
};

function renderContent(data: ProductDetailType | CategoryDetailType | StoreDetailType) {
    if (isProductType(data.__typename)) {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data.__typename === 'Category') {
        return <CategoryDetailPage category={data as CategoryDetailType} />;
    } else if (data.__typename === 'Store') {
        return <StoreDetailPage store={data as StoreDetailType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    const categoryDetailSort = getCategoryDetailSort(context.query.sort as string);
    const updatedPagination = GetNewPagination(Number(context.query.page));
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        NavigationQueryDocumentApi,
        friendlyUrlQuery(
            getUrlWithoutGetParameters(context.resolvedUrl),
            categoryDetailSort,
            updatedPagination.paginationCursor,
        ),
    ]);
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

const getCategoryDetailSort = (categoryDetailSortQuery: string): string => {
    return enabledSortTypes.includes(categoryDetailSortQuery) ? categoryDetailSortQuery : initialState.sort;
};

export default FriendlyUrlPage;
