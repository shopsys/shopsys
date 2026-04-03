import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { getCouldNotFindUserConsentPolicyArticleUrl } from 'components/Blocks/UserConsent/userConsentUtils';
import { Button } from 'components/Forms/Button/Button';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import logo from '/public/images/shopsys-logo.svg';

export const FooterCopyright: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [settingsResponse] = useSettingsQuery();
    const [userConsentUrl] = getInternationalizedStaticUrls(['/user-consent'], url);
    const currentYear = new Date().getFullYear();

    const handleBackToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <FooterContainer>
            <div className="flex flex-col items-center gap-5 lg:gap-2">
                <div
                    className="flex w-full items-center justify-center text-center text-sm text-text-default"
                    data-tid={TIDs.footer_copyright}
                >
                    {t('footerCopyright', { currentYear })}
                </div>

                <div className="flex items-center justify-center gap-1.5 text-sm text-text-default">
                    {t('Customized E-shop by')}

                    <a
                        aria-label={t('Visit Shopsys.com', { ns: 'accessibility' })}
                        href="https://www.shopsys.com"
                        rel="noreferrer"
                        tabIndex={0}
                        target="_blank"
                        title={t('Shopsys.com')}
                    >
                        <Image alt={t('Shopsys.com')} height="24" src={logo} />
                    </a>
                </div>

                {!getCouldNotFindUserConsentPolicyArticleUrl(settingsResponse) && (
                    <ExtendedNextLink
                        aria-label={t('Go to user consent update page', { ns: 'accessibility' })}
                        className="text-sm leading-6"
                        href={userConsentUrl}
                        skeletonType="user-consent"
                        title={t('User consent page')}
                    >
                        {t('Update your consent')}
                    </ExtendedNextLink>
                )}

                <Button
                    aria-label={t('Go to top of the page', { ns: 'accessibility' })}
                    className="w-full md:w-auto lg:hidden"
                    variant="secondary"
                    onClick={handleBackToTop}
                >
                    {t('Back to top')}
                </Button>
            </div>
        </FooterContainer>
    );
};
