import type { getClient } from '@sentry/nextjs';
import { routes } from 'config/routes';

type Integration = Parameters<NonNullable<ReturnType<typeof getClient>>['addIntegration']>[0];

const sensitiveParameterNames =
    /^(password|secret|api[_-]?key|(access_|refresh_)?token|e-?mail|[a-z0-9_-]*hash|[a-z0-9_-]*uuid)$/i;

const isSensitiveParameter = (name: string): boolean => {
    try {
        return sensitiveParameterNames.test(decodeURIComponent(name));
    } catch {
        return true;
    }
};

// Derive localized capability URLs from the router so translated slugs stay covered.
const sensitivePaths = Array.from(new Set(routes.flatMap((domainRoutes) => Object.values(domainRoutes))))
    .filter((route) =>
        route.split('/').some((segment) => segment.startsWith(':') && isSensitiveParameter(segment.slice(1))),
    )
    .map((route) => {
        const prefix = route.slice(0, route.lastIndexOf('/') + 1);
        const escapedPrefix = prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return new RegExp(`(${escapedPrefix})[^/?#\\s"'<>]+`, 'gi');
    });

const scrubString = (value: string): string => {
    const scrubbedQuery = value.replace(/(^|[?&#])([^?&#=\s"'<>]+)=([^?&#\s"'<>]*)/g, (match, separator, name) =>
        isSensitiveParameter(name) ? `${separator}${name}=[Filtered]` : match,
    );

    return sensitivePaths.reduce((result, path) => result.replace(path, '$1[Filtered]'), scrubbedQuery);
};

export const scrubSentryData = <T>(value: T): T =>
    JSON.parse(
        JSON.stringify(value, (key, item) => {
            if (isSensitiveParameter(key)) {
                return '[Filtered]';
            }

            return typeof item === 'string' ? scrubString(item) : item;
        }),
    );

// Feedback events bypass beforeSend, including the page URL attached by the feedback widget.
export const sentryFeedbackUrlPrivacyIntegration = (): Integration => ({
    name: 'StorefrontFeedbackUrlPrivacy',
    processEvent: (event) => (event.type === 'feedback' ? scrubSentryData(event) : event),
});
