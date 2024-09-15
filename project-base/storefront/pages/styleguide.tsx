import { CommonLayout } from 'components/Layout/CommonLayout';
import { StyleguideContent } from 'components/Pages/Styleguide/StyleguideContent';
import { isEnvironment } from 'utils/isEnvironment';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

type StyleguidePageProps = ServerSidePropsType & {
    iconList?: string[];
    tailwindColors?: Record<string, any>;
};

const StyleguidePage: FC<StyleguidePageProps> = ({ iconList, tailwindColors }) => {
    return (
        <CommonLayout title="Styleguide">
            <StyleguideContent iconList={iconList} tailwindColors={tailwindColors} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    let iconList: string[] = [];
    let tailwindColors: Record<string, any> | undefined = undefined;

    if (isEnvironment('development')) {
        const fsModule = await import('fs');
        const pathModule = await import('path');
        iconList = fsModule.readdirSync(pathModule.join(process.cwd(), '/components/Basic/Icon'));

        const resolveConfig = await import('tailwindcss/resolveConfig');
        const tailwindConfigRaw = await import('tailwind.config.js');
        const fullConfig = resolveConfig.default(tailwindConfigRaw);
        tailwindColors = fullConfig.theme.backgroundColor;
    }

    return await initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        additionalProps: isEnvironment('development') ? { iconList, tailwindColors } : {},
    });
});

export default StyleguidePage;
