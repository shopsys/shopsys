import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { ParsedErrors } from 'types/error';
import {
    ApplicationErrorsType,
    isFlashMessageError,
    isNoFlashMessageError,
    isNoLogError,
} from 'utils/errors/applicationErrors';

export type ErrorDecision =
    | { action: 'toast'; message: string; errorType: ApplicationErrorsType; severity: 'error' | 'warning' }
    | { action: 'form-field'; fieldName: string; message: string }
    | { action: 'silent-log' }
    | { action: 'ignore' }
    | { action: 'custom'; handler: () => void };

export type ErrorContext = {
    source: 'errorExchange' | 'form' | 'hook' | 'component';
    gtmOrigin?: GtmMessageOriginType;
    formFieldNames?: string[];
};

type CustomErrorHandler = (context: ErrorContext) => ErrorDecision;

type OrchestratorConfig = {
    deduplicationWindowMs: number;
    customHandlers: Map<ApplicationErrorsType, CustomErrorHandler>;
};

class ErrorOrchestratorImpl {
    private recentErrors = new Map<string, number>();
    private config: OrchestratorConfig;

    constructor(config: Partial<OrchestratorConfig> = {}) {
        this.config = {
            deduplicationWindowMs: config.deduplicationWindowMs ?? 2000,
            customHandlers: config.customHandlers ?? new Map(),
        };
    }

    public decide(parsed: ParsedErrors, context: ErrorContext): ErrorDecision[] {
        const decisions: ErrorDecision[] = [];

        // Handle validation errors
        if (parsed.userError?.validation) {
            for (const fieldName in parsed.userError.validation) {
                const error = parsed.userError.validation[fieldName];
                if (!error) {
                    continue;
                }

                if (context.formFieldNames?.includes(fieldName)) {
                    decisions.push({ action: 'form-field', fieldName, message: error.message });
                } else {
                    // Non-form validation error -> toast
                    if (!this.isDuplicate(`validation:${fieldName}`)) {
                        decisions.push({
                            action: 'toast',
                            message: error.message,
                            errorType: 'default',
                            severity: 'error',
                        });
                    }
                }
            }
        }

        // Handle application error
        if (parsed.applicationError) {
            const { type, message } = parsed.applicationError;

            // Check custom handler first
            const customHandler = this.config.customHandlers.get(type);
            if (customHandler) {
                decisions.push(customHandler(context));
                return decisions;
            }

            // Check deduplication
            if (this.isDuplicate(`app:${type}`)) {
                return decisions;
            }

            // Apply verbosity rules
            if (isNoLogError(type)) {
                decisions.push({ action: 'ignore' });
            } else if (isNoFlashMessageError(type)) {
                decisions.push({ action: 'silent-log' });
            } else if (isFlashMessageError(type)) {
                decisions.push({ action: 'toast', message, errorType: type, severity: 'error' });
            }
        }

        // Handle network error
        if (parsed.networkError) {
            if (!this.isDuplicate('network-error')) {
                decisions.push({
                    action: 'toast',
                    message: parsed.networkError,
                    errorType: 'default',
                    severity: 'error',
                });
            }
        }

        return decisions;
    }

    private isDuplicate(key: string): boolean {
        const now = Date.now();
        const lastSeen = this.recentErrors.get(key);

        if (lastSeen && now - lastSeen < this.config.deduplicationWindowMs) {
            return true;
        }

        this.recentErrors.set(key, now);
        this.cleanupOldErrors(now);
        return false;
    }

    private cleanupOldErrors(now: number): void {
        this.recentErrors.forEach((timestamp, key) => {
            if (now - timestamp > this.config.deduplicationWindowMs * 2) {
                this.recentErrors.delete(key);
            }
        });
    }
}

// Singleton instance
export const ErrorOrchestrator = new ErrorOrchestratorImpl();
