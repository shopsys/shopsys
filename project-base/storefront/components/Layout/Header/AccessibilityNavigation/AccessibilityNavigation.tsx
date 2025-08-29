import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import useTranslation from 'next-translate/useTranslation';

type AccessibilityNavigationProps = {
    simpleHeader?: boolean;
};

export const AccessibilityNavigation: FC<AccessibilityNavigationProps> = ({ simpleHeader }) => {
    const { t } = useTranslation();

    return (
        <nav aria-label={t('Skip navigation')}>
            <ul>
                <li>
                    <AccessibleLink href="#main-content" title={t('Skip to main content')} />
                </li>
                {!simpleHeader && (
                    <>
                        <li>
                            <AccessibleLink href="#search-input" title={t('Skip to search')} />
                        </li>
                        <li>
                            <AccessibleLink href="#main-navigation" title={t('Skip to navigation')} />
                        </li>
                    </>
                )}
            </ul>
        </nav>
    );
};
