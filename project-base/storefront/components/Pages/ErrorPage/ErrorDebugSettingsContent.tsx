import { IgnoredErrorsManager } from 'components/Blocks/IgnoredErrorsManager/IgnoredErrorsManager';
import { StyleguidePreview, StyleguideSubSection } from 'components/Pages/Styleguide/StyleguideElements';
import { IGNORED_ERRORS_KEY } from 'utils/errors/ignoredErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ErrorDebugSettingsContent: FC = () => {
    const { t } = useTranslation();

    return (
        <StyleguideSubSection
            title={t('Ignored Errors Manager')}
            description={
                <>
                    {t(
                        'Errors added to this list will not show as toasts. They will only log to console with [Ignored Error] prefix. Clear localStorage key',
                    )}{' '}
                    <code className="rounded px-1">{IGNORED_ERRORS_KEY}</code> {t('to reset.')}
                </>
            }
        >
            <StyleguidePreview>
                <IgnoredErrorsManager />
            </StyleguidePreview>
        </StyleguideSubSection>
    );
};
