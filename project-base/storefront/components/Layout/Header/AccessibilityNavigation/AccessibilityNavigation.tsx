import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type AccessibilityNavigationProps = {
    simpleHeader?: boolean;
};

export const AccessibilityNavigation: FC<AccessibilityNavigationProps> = ({ simpleHeader }) => {
    const { t } = useTranslation();

    return (
        <nav
            aria-label={t('Skip navigation', { ns: 'accessibility' })}
            className="focus-visible:outline-hidden"
            id="skip-navigation"
            tabIndex={-1}
        >
            <ul>
                <li>
                    <AccessibleLink href="#main-content" title={t('Skip to main content', { ns: 'accessibility' })} />
                </li>
                {!simpleHeader && (
                    <>
                        <li>
                            <AccessibleLink href="#search-input" title={t('Skip to search', { ns: 'accessibility' })} />
                        </li>
                        <li>
                            <AccessibleLink
                                href="#main-navigation"
                                title={t('Skip to navigation', { ns: 'accessibility' })}
                            />
                        </li>
                    </>
                )}
            </ul>
        </nav>
    );
};
