import { IgnoredErrorsManager } from 'components/Blocks/IgnoredErrorsManager/IgnoredErrorsManager';
import { Webline } from 'components/Layout/Webline/Webline';
import { StyleguideSection } from 'components/Pages/Styleguide/StyleguideElements';
import { IGNORED_ERRORS_KEY } from 'utils/errors/ignoredErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isEnvironment } from 'utils/isEnvironment';

export const ErrorDebugSettingsContent: FC = () => {
    const { t } = useTranslation();

    if (!isEnvironment('development')) {
        return null;
    }

    return (
        <Webline className="mb-10 flex flex-col gap-10">
            <StyleguideSection className="flex flex-col gap-5" title={t('Error Debug Settings')}>
                <div className="border-border-accent bg-background-more rounded-lg border-2 p-6">
                    <h2 className="mb-4 text-lg font-semibold">{t('Ignored Errors Manager')}</h2>

                    <p className="text-text-secondary mb-4 text-sm">
                        {t(
                            'Errors added to this list will not show as toasts. They will only log to console with [Ignored Error] prefix. Clear localStorage key',
                        )}{' '}
                        <code className="rounded px-1">{IGNORED_ERRORS_KEY}</code> {t('to reset.')}
                    </p>
                    <IgnoredErrorsManager />
                </div>
            </StyleguideSection>
        </Webline>
    );
};
