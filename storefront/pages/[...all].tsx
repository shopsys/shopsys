import { friendlyUrlQuery, getFriendlyUrlResolvedData } from '../connectors/friendlyUrls/FriendlyUrls';
import Breadcrumbs from 'components/layout/Breadcrumbs';
import CategoryDetailPage from '../components/pages/CategoryDetail';
import { CategoryDetailType } from '../components/pages/CategoryDetail/types';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import ProductDetailPage from '../components/pages/ProductDetail';
import { ProductDetailType } from 'components/pages/ProductDetail/types';
import { useRouter } from 'next/router';

const FriendlyUrlPage: FC = () => {
    const router = useRouter();

    const data = getFriendlyUrlResolvedData(router.asPath);

    return (
        <>
            {data && <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />}
            {renderContent(data)}
        </>
    );
};

function renderContent(data: ProductDetailType | CategoryDetailType | undefined | null) {
    if (data?.__typename === 'RegularProduct' || data?.__typename === 'MainVariant' || data?.__typename === 'Variant') {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data?.__typename === 'Category') {
        return <CategoryDetailPage category={data as CategoryDetailType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [friendlyUrlQuery(getUrlWithoutGetParameters(context.resolvedUrl))]);
};

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

export default FriendlyUrlPage;
