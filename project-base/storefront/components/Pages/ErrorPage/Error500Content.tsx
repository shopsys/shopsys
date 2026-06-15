import { LinkButton } from 'components/Forms/Button/LinkButton';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ErrorPage } from './ErrorPage';

type Error500ContentProps = {
    err?: string;
};

export const Error500Content: FC<Error500ContentProps> = ({ err }) => {
    const { t } = useTranslation();

    return (
        <ErrorLayout>
            <ErrorPage
                heading={t('Something went wrong.')}
                statusCode="500"
                text={t('Please try again later or contact us.')}
            >
                <div className="flex justify-center">
                    <LinkButton href="/" skeletonType="homepage">
                        {t('Back to shop')}
                    </LinkButton>
                </div>
            </ErrorPage>

            {isWithToastAndConsoleErrorDebugging && err && (
                <div className="my-3 flex justify-center">
                    <div className="rounded-sm bg-background-more">
                        <div className="p-3">
                            <p>{err}</p>
                        </div>
                    </div>
                </div>
            )}
        </ErrorLayout>
    );
};
