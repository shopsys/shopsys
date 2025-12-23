import { Button } from 'components/Forms/Button/Button';
import { useEffect, useState } from 'react';
import { clearIgnoredErrors, getIgnoredErrors, IgnoredErrorEntry, unignoreError } from 'utils/errors/ignoredErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const IgnoredErrorsManager: FC = () => {
    const { t } = useTranslation();
    const [ignoredErrors, setIgnoredErrors] = useState<IgnoredErrorEntry[]>([]);

    useEffect(() => {
        setIgnoredErrors(getIgnoredErrors());
    }, []);

    const handleClearAll = () => {
        clearIgnoredErrors();
        setIgnoredErrors([]);
    };

    const handleUnignore = (identifier: string) => {
        unignoreError(identifier);
        setIgnoredErrors(getIgnoredErrors());
    };

    if (ignoredErrors.length === 0) {
        return (
            <div className="border-border-default bg-background-accent rounded border p-4">
                <p className="text-text-inverted">
                    {t('No ignored errors. Click "Ignore this error type" on any error to add it here.')}
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <p className="text-text-secondary">
                    {t('{{ count }} error type(s) being ignored', { count: ignoredErrors.length })}
                </p>
                <Button size="small" variant="inverted" onClick={handleClearAll}>
                    {t('Clear all')}
                </Button>
            </div>

            <div className="space-y-2">
                {ignoredErrors.map((entry) => (
                    <div
                        key={entry.identifier}
                        className="border-border-default bg-background-accent flex items-center justify-between gap-4 rounded border p-3"
                    >
                        <div className="min-w-0 flex-1">
                            <p className="text-text-inverted truncate font-mono text-sm font-semibold">
                                {entry.identifier}
                            </p>
                            <p className="text-text-inverted truncate text-xs opacity-80">{entry.sampleMessage}</p>
                            <p className="text-text-inverted text-xs opacity-60">
                                {t('Ignored')}: {new Date(entry.ignoredAt).toLocaleString()}
                            </p>
                        </div>
                        <button
                            className="text-text-inverted shrink-0 cursor-pointer text-xs underline opacity-80 hover:opacity-100"
                            type="button"
                            onClick={() => handleUnignore(entry.identifier)}
                        >
                            {t('Restore')}
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
};
