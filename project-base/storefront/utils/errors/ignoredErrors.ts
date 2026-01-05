export const IGNORED_ERRORS_KEY = 'ignored-debug-errors';

export type IgnoredErrorEntry = {
    identifier: string;
    ignoredAt: string;
    sampleMessage: string;
};

export const getErrorIdentifier = (userCode: string | null, message: string): string => {
    return userCode ?? message;
};

export const getIgnoredErrors = (): IgnoredErrorEntry[] => {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const stored = localStorage.getItem(IGNORED_ERRORS_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch {
        return [];
    }
};

export const isErrorIgnored = (identifier: string): boolean => {
    const ignored = getIgnoredErrors();
    return ignored.some((entry) => entry.identifier === identifier);
};

export const ignoreError = (identifier: string, sampleMessage: string): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const ignored = getIgnoredErrors();

    // Don't add duplicates
    if (ignored.some((entry) => entry.identifier === identifier)) {
        return;
    }

    const newEntry: IgnoredErrorEntry = {
        identifier,
        ignoredAt: new Date().toISOString(),
        sampleMessage,
    };

    localStorage.setItem(IGNORED_ERRORS_KEY, JSON.stringify([...ignored, newEntry]));
};

export const unignoreError = (identifier: string): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const ignored = getIgnoredErrors();
    const filtered = ignored.filter((entry) => entry.identifier !== identifier);
    localStorage.setItem(IGNORED_ERRORS_KEY, JSON.stringify(filtered));
};

export const clearIgnoredErrors = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.removeItem(IGNORED_ERRORS_KEY);
};
