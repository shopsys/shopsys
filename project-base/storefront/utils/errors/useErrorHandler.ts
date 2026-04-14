import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useCallback } from 'react';
import { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { ApplicationErrorsType } from './applicationErrors';
import { ErrorContext, ErrorDecision, ErrorOrchestrator } from './ErrorOrchestrator';
import { getUserFriendlyErrors } from './friendlyErrorMessageParser';
import { logException } from './logException';

type UseErrorHandlerOptions<TFormValues extends FieldValues> = {
    form?: UseFormReturn<TFormValues>;
    gtmOrigin?: GtmMessageOriginType;
    customMessage?: string;
    customHandlers?: Partial<Record<ApplicationErrorsType, () => void>>;
};

type ErrorHandlerResult = {
    success: boolean;
    errorType?: ApplicationErrorsType;
    decisions?: ErrorDecision[];
};

export function useErrorHandler<TFormValues extends FieldValues = FieldValues>(
    options: UseErrorHandlerOptions<TFormValues> = {},
): (error: CombinedError | undefined) => ErrorHandlerResult {
    const { t } = useTranslation();
    const { form, gtmOrigin, customMessage, customHandlers } = options;

    return useCallback(
        (error: CombinedError | undefined): ErrorHandlerResult => {
            if (!error) {
                return { success: true };
            }

            const parsed = getUserFriendlyErrors(error, t);

            const context: ErrorContext = {
                source: form ? 'form' : 'hook',
                gtmOrigin: gtmOrigin ?? GtmMessageOriginType.other,
                formFieldNames: form ? Object.keys(form.getValues()) : undefined,
            };

            const decisions = ErrorOrchestrator.decide(parsed, context);

            // Execute decisions
            for (const decision of decisions) {
                switch (decision.action) {
                    case 'toast':
                        showErrorMessage(
                            decision.origin === 'application' && customMessage ? customMessage : decision.message,
                            context.gtmOrigin,
                            {
                                errorType: decision.errorType,
                            },
                        );
                        break;

                    case 'form-field':
                        form?.setError(decision.fieldName as Path<TFormValues>, {
                            message: decision.message,
                        });
                        break;

                    case 'silent-log':
                        logException({
                            message: error.message,
                            parsedApplicationError: parsed.applicationError,
                            originalError: JSON.stringify(error),
                            location: 'useErrorHandler',
                        });
                        break;

                    case 'custom':
                        decision.handler();
                        break;

                    case 'ignore':
                        // Do nothing
                        break;
                }
            }

            // Execute any component-specific custom handlers
            if (customHandlers && parsed.applicationError) {
                const handler = customHandlers[parsed.applicationError.type];
                handler?.();
            }

            return {
                success: false,
                errorType: parsed.applicationError?.type,
                decisions,
            };
        },
        [t, form, gtmOrigin, customMessage, customHandlers],
    );
}
