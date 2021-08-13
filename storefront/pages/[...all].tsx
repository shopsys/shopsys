import { friendlyUrlQuery, getFriendlyUrlResolvedData } from '../connectors/friendlyUrls/FriendlyUrls';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import ProductDetailPage from '../components/pages/ProductDetail';
import { ProductDetailType } from '../connectors/products/ProductDetailType';
import { useRouter } from 'next/router';

const FriendlyUrlPage: FC = () => {
    const router = useRouter();

    const data = getFriendlyUrlResolvedData(router.asPath);

    return <>{renderContent(data)}</>;
};

function renderContent(data: ProductDetailType | undefined | null) {
    if (data === undefined || data === null) {
        return <DefaultErrorPage statusCode={404} />;
    }

    if (data.__typename === 'RegularProduct') {
        return <ProductDetailPage data={data} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [friendlyUrlQuery(context.resolvedUrl)]);
};

export default FriendlyUrlPage;
