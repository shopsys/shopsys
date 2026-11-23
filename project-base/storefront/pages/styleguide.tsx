import { CommonLayout } from 'components/Layout/CommonLayout';
import { StyleguideContent } from 'components/Pages/Styleguide/StyleguideContent';
import { parseThemeColorBlocks, ThemeColorBlock } from 'components/Pages/Styleguide/themeColors';
import { isEnvironment } from 'utils/isEnvironment';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

type StyleguidePageProps = ServerSidePropsType & {
    themeColorBlocks?: ThemeColorBlock[];
};

const StyleguidePage: FC<StyleguidePageProps> = ({ themeColorBlocks }) => {
    return (
        <CommonLayout defaultTitle="Styleguide">
            <StyleguideContent themeColorBlocks={themeColorBlocks} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    let themeColorBlocks: ThemeColorBlock[] | undefined;

    if (isEnvironment('development')) {
        const fsModule = await import('node:fs');
        const pathModule = await import('node:path');

        const cssContent = fsModule.readFileSync(pathModule.join(process.cwd(), '/styles/theme.css'));
        themeColorBlocks = parseThemeColorBlocks(cssContent.toString());
    }

    return await initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        additionalProps: isEnvironment('development') ? { themeColorBlocks } : {},
    });
});

export default StyleguidePage;
