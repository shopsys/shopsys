import { CommonLayout } from 'components/Layout/CommonLayout';
import { ErrorDebugSettingsContent } from 'components/Pages/ErrorPage/ErrorDebugSettingsContent';
import { StyleguideContent } from 'components/Pages/Styleguide/StyleguideContent';
import { isEnvironment } from 'utils/isEnvironment';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

type StyleguidePageProps = ServerSidePropsType & {
    tailwindColors?: Record<string, any>;
};

const StyleguidePage: FC<StyleguidePageProps> = ({ tailwindColors }) => {
    return (
        <CommonLayout title="Styleguide">
            <StyleguideContent tailwindColors={tailwindColors} />

            <ErrorDebugSettingsContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    let tailwindColors: Record<string, any> | undefined;

    if (isEnvironment('development')) {
        const fsModule = await import('node:fs');
        const pathModule = await import('node:path');

        const cssContent = fsModule.readFileSync(pathModule.join(process.cwd(), '/styles/theme.css'));
        tailwindColors = cssContent
            .toString()
            .split('\n')
            .filter((line) => line.trim().startsWith('--color-'))
            .reduce((acc, line) => {
                const [key, value] = line.split(':').map((part) => part.trim());
                const cleanKey = key.replace('--color-', '');

                return {
                    ...acc,
                    [cleanKey]: value.replace(';', ''),
                };
            }, {});
    }

    return await initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        additionalProps: isEnvironment('development') ? { tailwindColors } : {},
    });
});

export default StyleguidePage;
