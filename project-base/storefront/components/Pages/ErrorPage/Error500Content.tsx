import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain, ErrorPageButtonLink } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type Error500ContentProps = {
    err?: string;
};

export const Error500Content: FC<Error500ContentProps> = ({ err }) => {
    const { t } = useTranslation();

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPage isWithoutImage>
                    <ErrorPageTextHeading>{t('Something went wrong.')}</ErrorPageTextHeading>
                    <ErrorPageTextMain>{t('Please try again later or contact us.')}</ErrorPageTextMain>
                    <ErrorPageButtonLink href="/">{t('Back to shop')}</ErrorPageButtonLink>
                </ErrorPage>
                {isWithToastAndConsoleErrorDebugging && err && (
                    <div className="my-3 flex justify-center">
                        <div className="bg-background-more rounded-sm">
                            <div className="p-3">
                                <p>{err}</p>
                            </div>
                        </div>
                    </div>
                )}
            </Webline>
        </ErrorLayout>
    );
};
