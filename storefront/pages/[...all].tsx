import { friendlyUrlQuery, getFriendlyUrlResolvedData } from '../connectors/friendlyUrls/FriendlyUrls';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { CategoryDetailApiType } from '../components/Pages/CategoryDetail/types';
import CategoryDetailPage from '../components/Pages/CategoryDetail';
import CommonLayout from '../components/Layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import { mapCategoryDetailData } from '../connectors/categories/Categories';
import { navigationQuery } from '../connectors/navigation/Navigation';
import ProductDetailPage from '../components/Pages/ProductDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { useRouter } from 'next/router';
import { useShopsysSelector } from '../redux/store';
import Webline from '../components/Layout/Webline';

const FriendlyUrlPage: FC = () => {
    const router = useRouter();
    const data = getFriendlyUrlResolvedData(router.asPath);

    return (
        <CommonLayout>
            <Webline>
                {data && <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />}
                {renderContent(data)}
            </Webline>
        </CommonLayout>
    );
};

function renderContent(data: ProductDetailType | CategoryDetailApiType | undefined | null) {
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.__typename === 'RegularProduct' || data?.__typename === 'MainVariant' || data?.__typename === 'Variant') {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data?.__typename === 'Category') {
        return (
            <CategoryDetailPage
                category={mapCategoryDetailData(data as CategoryDetailApiType, currentDomainConfig.currencyCode)}
            />
        );
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [
        navigationQuery,
        friendlyUrlQuery(getUrlWithoutGetParameters(context.resolvedUrl)),
    ]);
};

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

export default FriendlyUrlPage;
