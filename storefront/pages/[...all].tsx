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
import { navigationQuery } from 'connectors/navigation/Navigation';
import ProductDetailPage from 'components/Pages/ProductDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useRouter } from 'next/router';
import Webline from 'components/Layout/Webline';

const FriendlyUrlPage: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const categoryDetailSortQuery = router.query.sort as string;
    const categoryDetailSortState = useShopsysSelector((state) => state.user.sort);
    const categoryDetailSort = getCategoryDetailSort(
        typeof categoryDetailSortQuery !== 'undefined' ? categoryDetailSortQuery : categoryDetailSortState,
    );

    useEffect(() => {
        dispatch(userActions.setSort({ sort: categoryDetailSort as SortType }));
    }, [categoryDetailSortQuery]);
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

function renderContent(data: ProductDetailType | CategoryDetailType) {
    if (isProductType(data.__typename)) {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data.__typename === 'Category') {
        return <CategoryDetailPage category={data as CategoryDetailType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    const categoryDetailSort = getCategoryDetailSort(context.query.sort as string);
    return initServerSideProps(context, store, [
        navigationQuery,
        friendlyUrlQuery(getUrlWithoutGetParameters(context.resolvedUrl), categoryDetailSort),
    ]);
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

const getCategoryDetailSort = (categoryDetailSortQuery: string): string => {
    return enabledSortTypes.includes(categoryDetailSortQuery) ? categoryDetailSortQuery : initialState.sort;
};

export default FriendlyUrlPage;
