import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { useId } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type UserConsentPolicyLinkProps = {
    className?: string;
};

export const UserConsentPolicyLink: FC<UserConsentPolicyLinkProps> = ({ className }) => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const userConsentPolicyArticleUrl = settingsData?.settings?.userConsentPolicyArticleUrl;
    const descriptionId = useId();

    return (
        <>
            <span className="sr-only" id={descriptionId}>
                {t('This page is about the consent and tracking policy. You can read more about it here.')}
            </span>

            <Trans
                defaultTrans="To learn more, you can read our <link>consent and tracking policy</link>"
                i18nKey="userConsentPolicyLink"
                components={{
                    link: userConsentPolicyArticleUrl ? (
                        <ExtendedNextLink
                            aria-describedby={descriptionId}
                            className={className}
                            href={userConsentPolicyArticleUrl}
                            rel="noreferrer"
                            tabIndex={0}
                            target="_blank"
                            title={t('Consent and tracking policy')}
                        />
                    ) : (
                        <span />
                    ),
                }}
            />
        </>
    );
};
