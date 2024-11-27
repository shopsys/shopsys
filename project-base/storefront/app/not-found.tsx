import { Error404Headless } from 'components/Pages/ErrorPage/Error404Headless';
import { getServerT } from 'utils/getServerTranslation';

export default async function NotFound() {
    const t = await getServerT();

    return (
        <Error404Headless
            backButtonHref="/app"
            backButtonText={t('Back to shop')}
            headingText={t('We have nothing to show you at this url.')}
            imageAlt={t('404')}
            mainText={t('But at other addresses we have a lot for you...')}
        />
    );
}
