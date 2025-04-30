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

        const cssContent = fsModule.readFileSync(pathModule.join(process.cwd(), '/styles/globals.css'));
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
        additionalProps: isEnvironment('development') ? { iconList, tailwindColors } : {},
    });
});

export default StyleguidePage;
