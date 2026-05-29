import { CommonLayout } from 'components/Layout/CommonLayout';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ErrorPage } from './ErrorPage';
import { ErrorPageUsefulLinks } from './ErrorPageUsefulLinks';

export const Error403Content: FC = () => {
    const { t } = useTranslation();

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <ErrorPage
                heading={t('Access denied')}
                statusCode="403"
                text={t("Sorry, you don't have permission to access this page, but you can continue from here.")}
            >
                <div data-tid={TIDs.error_403_page} className="w-full">
                    <ErrorPageUsefulLinks />
                </div>
            </ErrorPage>
        </CommonLayout>
    );
};
