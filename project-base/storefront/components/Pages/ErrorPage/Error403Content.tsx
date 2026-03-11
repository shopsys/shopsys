import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const Error403Content: FC = () => {
    const { t } = useTranslation();

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <Webline>
                <ErrorPage>
                    <div data-tid={TIDs.error_403_page}>
                        <ErrorPageTextHeading>{t('403 Forbidden')}</ErrorPageTextHeading>
                        <ErrorPageTextMain>
                            {t("Sorry, you don't have permission to access this page.")}
                        </ErrorPageTextMain>

                        <ErrorPageButtonLink href="/" skeletonType="homepage">
                            {t('Back to shop')}
                        </ErrorPageButtonLink>
                    </div>
                </ErrorPage>
            </Webline>
        </CommonLayout>
    );
};
