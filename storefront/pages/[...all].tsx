import { friendlyUrlQuery, getFriendlyUrlResolvedData } from '../connectors/friendlyUrls/FriendlyUrls';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import ProductDetailPage from '../components/pages/ProductDetail';
import { ProductDetailType } from '../components/pages/ProductDetail/types';
import { useRouter } from 'next/router';

const FriendlyUrlPage: FC = () => {
    const router = useRouter();

    const data = getFriendlyUrlResolvedData(router.asPath);

    return <>{renderContent(data)}</>;
};

function renderContent(data: ProductDetailType | undefined | null) {
    if (data?.__typename === 'RegularProduct') {
        return <ProductDetailPage product={data as ProductDetailType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [friendlyUrlQuery(context.resolvedUrl)]);
};

export default FriendlyUrlPage;
