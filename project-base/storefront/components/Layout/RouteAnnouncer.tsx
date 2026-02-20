import { useRouter } from 'next/router';
import { startTransition, useCallback, useEffect, useEffectEvent, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const ANNOUNCEMENT_MAX_WAIT_MS = 4000;
const ANNOUNCEMENT_POLL_INTERVAL_MS = 80;
const ANNOUNCEMENT_EARLY_REPEAT_THRESHOLD_MS = 400;

const readDocumentTitle = (): string => {
    const titleFromHead = document.head.querySelector('title')?.textContent ?? '';
    const fallbackTitle = document.title;
    return (titleFromHead || fallbackTitle).replace(/\s+/g, ' ').trim();
};

type AnnounceOptions = {
    forceRepeat?: boolean;
    formatter?: (normalizedTitle: string) => string;
};

export const RouteAnnouncer: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();

    const { hadClientSideNavigation, isPageLoading } = useSessionStore(
        useCallback(
            (state) => ({
                hadClientSideNavigation: state.hadClientSideNavigation,
                isPageLoading: state.isPageLoading,
            }),
            [],
        ),
    );

    const [message, setMessage] = useState<string>('');
    const lastAnnouncedRef = useRef<string>('');
    const pendingPollRef = useRef<number | null>(null);
    const pendingDeferredAnnouncementRef = useRef<number | null>(null);
    const loadingTitleRef = useRef<string | null>(null);

    const clearTimers = useCallback(() => {
        if (pendingPollRef.current) {
            window.clearTimeout(pendingPollRef.current);
            pendingPollRef.current = null;
        }
        if (pendingDeferredAnnouncementRef.current) {
            window.clearTimeout(pendingDeferredAnnouncementRef.current);
            pendingDeferredAnnouncementRef.current = null;
        }
    }, []);

    const announceTitle = useCallback(
        (title: string, options: AnnounceOptions = {}) => {
            const normalized = title.trim();

            if (!normalized) {
                return;
            }

            if (!options.forceRepeat && normalized === lastAnnouncedRef.current) {
                return;
            }

            pendingDeferredAnnouncementRef.current = window.setTimeout(() => {
                startTransition(() => {
                    lastAnnouncedRef.current = normalized;
                    const resolvedMessage = options.formatter
                        ? options.formatter(normalized)
                        : t('You are on {{pageTitle}} page', { ns: 'accessibility', pageTitle: normalized });
                    setMessage(resolvedMessage);
                });
            }, 50);
        },
        [t],
    );

    const scheduleTitleAnnouncement = useCallback(
        (options: { forceRepeat?: boolean } = {}) => {
            clearTimers();

            const startedAt = performance.now();
            const initialTitle = readDocumentTitle();

            const pollForTitle = () => {
                const currentTitle = readDocumentTitle();
                const titleChanged = currentTitle && currentTitle !== initialTitle;
                const elapsed = performance.now() - startedAt;
                const timedOut = performance.now() - startedAt >= ANNOUNCEMENT_MAX_WAIT_MS;
                const exceedEarlyRepeatThreshold =
                    !titleChanged && elapsed >= ANNOUNCEMENT_EARLY_REPEAT_THRESHOLD_MS && !!currentTitle;

                if (titleChanged || exceedEarlyRepeatThreshold || timedOut) {
                    const candidateTitle = currentTitle || initialTitle;
                    const shouldUseLoadingFormatter =
                        loadingTitleRef.current !== null && candidateTitle === loadingTitleRef.current;

                    announceTitle(candidateTitle, {
                        forceRepeat: options.forceRepeat || exceedEarlyRepeatThreshold || timedOut,
                        formatter: shouldUseLoadingFormatter
                            ? () => loadingTitleRef.current ?? candidateTitle
                            : undefined,
                    });

                    if (titleChanged) {
                        loadingTitleRef.current = null;
                    }

                    return;
                }

                pendingPollRef.current = window.setTimeout(pollForTitle, ANNOUNCEMENT_POLL_INTERVAL_MS);
            };

            pendingPollRef.current = window.setTimeout(pollForTitle, ANNOUNCEMENT_POLL_INTERVAL_MS);
        },
        [announceTitle, clearTimers],
    );

    const onInitialAnnounce = useEffectEvent(() => {
        const toastTextElement = document.querySelector(
            '[data-tid="toast_success"], [data-tid="toast_error"], [data-tid="toast_info"]',
        );

        if (toastTextElement instanceof HTMLElement) {
            toastTextElement.setAttribute('tabindex', '-1');
            toastTextElement.focus();
        } else {
            announceTitle(readDocumentTitle(), { forceRepeat: true });
        }
    });

    useEffect(() => {
        onInitialAnnounce();
    }, []);

    const onCleanup = useEffectEvent(() => clearTimers());
    const onSchedule = useEffectEvent(() => scheduleTitleAnnouncement({ forceRepeat: true }));

    useEffect(() => {
        if (!hadClientSideNavigation) {
            return undefined;
        }

        if (isPageLoading) {
            onCleanup();
            return undefined;
        }

        onSchedule();

        return () => onCleanup();
    }, [hadClientSideNavigation, isPageLoading]);

    const onRouteChangeStart = useEffectEvent(() => {
        clearTimers();

        const loadingText = t('Page loading', { ns: 'accessibility' });
        loadingTitleRef.current = loadingText;
        document.title = loadingText;
        lastAnnouncedRef.current = loadingText;
        setMessage(loadingText);
    });

    useEffect(() => {
        router.events.on('routeChangeStart', onRouteChangeStart);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
        };
    }, [router.events]);

    return (
        <div aria-atomic="true" aria-live="polite" className="sr-only" role="status">
            {message}
        </div>
    );
};
