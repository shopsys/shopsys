import { CommonLayout } from 'components/Layout/CommonLayout';
import { StyleguideContent } from 'components/Pages/Styleguide/StyleguideContent';
import { readdirSync } from 'fs';
import { join } from 'path';
import { isEnvironment } from 'utils/isEnvironment';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

type StyleguidePageProps = ServerSidePropsType & {
    iconList: string[];
};

const StyleguidePage: FC<StyleguidePageProps> = ({ iconList }) => {
    return (
        <CommonLayout title="Styleguide">
            <StyleguideContent iconList={iconList} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (isEnvironment('production')) {
        return {
            redirect: {
                destination: '/',
                permanent: false,
            },
        };
    }

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
