import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect, useRef, useState } from 'react';

type AccessibilityNavigationProps = {
    simpleHeader?: boolean;
};

export const AccessibilityNavigation: FC<AccessibilityNavigationProps> = ({ simpleHeader }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const accessibilityLinksRef = useRef<HTMLDivElement>(null);
    const previousRouteRef = useRef<string>('');
    const [pageTitle, setPageTitle] = useState('');

    // reset focus to accessibility menu only on actual route changes
    useEffect(() => {
        const handleRouteChangeComplete = (url: string) => {
            const newPathname = url.split('?')[0];

            // only focus if it's a different route (not just query param changes) AND not a link click
            if (newPathname !== previousRouteRef.current) {
                if (accessibilityLinksRef.current) {
                    accessibilityLinksRef.current.focus();
                }
            }

            previousRouteRef.current = newPathname;
        };

        previousRouteRef.current = router.asPath.split('?')[0];

        router.events.on('routeChangeComplete', handleRouteChangeComplete);

        return () => {
            router.events.off('routeChangeComplete', handleRouteChangeComplete);
        };
    }, [router.events]);

    // set page title after hydration to avoid hydration mismatch
    useEffect(() => {
        setPageTitle(document.title || '');
    }, []);

    const announceText = pageTitle ? t('You are on {{pageTitle}} page', { pageTitle }) : '';

    return (
        <>
            <span aria-atomic="true" aria-live="polite" className="sr-only" ref={accessibilityLinksRef} tabIndex={-1}>
                {announceText}
            </span>

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
        </>
    );
};
