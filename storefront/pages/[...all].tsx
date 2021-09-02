import { friendlyUrlQuery, getFriendlyUrlResolvedData } from '../connectors/friendlyUrls/FriendlyUrls';
import Breadcrumbs from 'components/layout/Breadcrumbs';
import { CategoryDetailApiType } from '../components/pages/CategoryDetail/types';
import CategoryDetailPage from '../components/pages/CategoryDetail';
import CommonLayout from '../components/layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import { mapCategoryDetailData } from '../connectors/categories/Categories';
import { navigationQuery } from '../connectors/navigation/Navigation';
import ProductDetailPage from '../components/pages/ProductDetail';
import { ProductDetailType } from 'components/pages/ProductDetail/types';
import { useRouter } from 'next/router';
import { useShopsysSelector } from '../redux/store';
import Webline from '../components/layout/Webline';

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
