import { CommonLayout } from 'components/Layout/CommonLayout';
import { StyleguideContent } from 'components/Pages/Styleguide/StyleguideContent';
import { readdirSync } from 'fs';
import { isEnvironment } from 'helpers/isEnvironment';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRouter } from 'next/router';
import { join } from 'path';

type StyleguidePageProps = ServerSidePropsType & {
    iconList: string[];
};

const StyleguidePage: FC<StyleguidePageProps> = ({ iconList }) => {
    const router = useRouter();

    if (isEnvironment('production')) {
        router.replace('/');
    }

    return (
        <CommonLayout title="Styleguide">
            <StyleguideContent iconList={iconList} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const iconList = readdirSync(join(process.cwd(), '/components/Basic/Icon'));

    return await initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        additionalProps: { iconList },
    });
});

export default StyleguidePage;
