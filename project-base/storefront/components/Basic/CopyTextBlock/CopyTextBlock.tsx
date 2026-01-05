import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Button } from 'components/Forms/Button/Button';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getErrorIdentifier, ignoreError } from 'utils/errors/ignoredErrors';
import {
    buildValidationDisplayMessage,
    getEffectiveErrorCode,
    hasValidationErrors,
    parseGraphqlErrorFromJson,
} from 'utils/errors/parseGraphqlError';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type CopyTextBlockProps = {
    textToCopy: string;
    onIgnore?: () => void;
};

type DisplayError = {
    errorCode: string | number | null;
    userCode: string | null;
    message: string;
    fullDetails: string;
    hasDetails: boolean;
};

const parseErrorForDisplay = (text: string): DisplayError => {
    const parsed = parseGraphqlErrorFromJson(text);

    if (parsed === null) {
        // Not valid JSON or not a GraphQL error structure
        return {
            errorCode: null,
            userCode: null,
            message: text,
            fullDetails: text,
            hasDetails: false,
        };
    }

    const formatted = JSON.stringify(JSON.parse(text), null, 2);
    const errorCode = getEffectiveErrorCode(parsed);

    // Use validation messages if the main message is generic "validation"
    const displayMessage =
        parsed.message === 'validation' && hasValidationErrors(parsed)
            ? buildValidationDisplayMessage(parsed.validationErrors!)
            : parsed.message;

    return {
        errorCode,
        userCode: parsed.userCode,
        message: displayMessage,
        fullDetails: formatted,
        hasDetails: true,
    };
};

type CopyButtonState = 'default' | 'copied' | 'failed';
type IgnoreButtonState = 'default' | 'ignored';

export const CopyTextBlock: FC<CopyTextBlockProps> = ({ textToCopy, onIgnore }) => {
    const { t } = useTranslation();
    const [copyButtonState, setCopyButtonState] = useState<CopyButtonState>('default');
    const [ignoreButtonState, setIgnoreButtonState] = useState<IgnoreButtonState>('default');
    const [isDetailsExpanded, setIsDetailsExpanded] = useState(false);

    const { errorCode, userCode, message, fullDetails, hasDetails } = parseErrorForDisplay(textToCopy);
    const errorIdentifier = getErrorIdentifier(userCode, message);

    const copyButtonTextMap = {
        default: t('Copy'),
        copied: t('Copied'),
        failed: t('Failed'),
    };

    const ignoreButtonTextMap = {
        default: t('Ignore this error type'),
        ignored: t('Ignored! Dismissing...'),
    };

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(fullDetails);
            setCopyButtonState('copied');
        } catch {
            setCopyButtonState('failed');
        }
    };

    const handleIgnore = () => {
        ignoreError(errorIdentifier, message);
        setIgnoreButtonState('ignored');
        setTimeout(() => {
            onIgnore?.();
        }, 500);
    };

    return (
        <div className="bg-background-default flex max-h-96 flex-col gap-3 overflow-auto rounded">
            <div className="px-3 pt-3">
                <p className="text-text-error break-words whitespace-pre-line">
                    {errorCode !== null && <span>{t('Error {{ errorCode }}', { errorCode })}: </span>}
                    {message}
                </p>
            </div>

            {hasDetails && (
                <div className="mx-3 rounded-xl">
                    <button
                        type="button"
                        className={twMergeCustom(
                            'border-border-less bg-background-more hover:text-button-inverted-text-hovered flex w-full cursor-pointer items-center justify-between rounded-md border p-2 text-left text-sm transition-[border-radius] duration-200 outline-none',
                            isDetailsExpanded && 'rounded-b-none',
                        )}
                        onClick={() => setIsDetailsExpanded(!isDetailsExpanded)}
                    >
                        <span>{t('Full error details')}</span>
                        <ArrowIcon
                            className={twJoin(
                                'size-4 transition-transform duration-200',
                                isDetailsExpanded && 'rotate-180',
                            )}
                        />
                    </button>

                    <div
                        className={twJoin(
                            'grid transition-[grid-template-rows] duration-200 ease-out',
                            isDetailsExpanded ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                        )}
                    >
                        <div className="overflow-hidden">
                            <pre className="border-border-less bg-background-more overflow-auto rounded-b-md border-x border-b p-3 text-xs break-words whitespace-pre-wrap">
                                {fullDetails}
                            </pre>
                        </div>
                    </div>
                </div>
            )}

            <div className="flex items-center justify-between gap-2 px-3 pb-2">
                <button
                    className="text-text-secondary hover:text-text-default cursor-pointer text-xs underline"
                    title={t('Ignore errors with identifier: {{ errorIdentifier }}', { errorIdentifier })}
                    type="button"
                    onClick={handleIgnore}
                >
                    {ignoreButtonTextMap[ignoreButtonState]}
                </button>
                <Button size="small" variant="inverted" onClick={handleCopy}>
                    {copyButtonTextMap[copyButtonState]}
                </Button>
            </div>
        </div>
    );
};
