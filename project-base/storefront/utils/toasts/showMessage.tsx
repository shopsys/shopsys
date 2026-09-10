import { CopyTextBlock } from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { RejectedIcon } from 'components/Basic/Icon/RejectedIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { TIDs } from 'cypress/tids';
import { ReactNode } from 'react';
import { toast } from 'react-toastify';
import { getErrorIdentifier, isErrorIgnored } from 'utils/errors/ignoredErrors';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import { parseGraphqlErrorFromJson } from 'utils/errors/parseGraphqlError';

const getErrorIdentifierFromMessage = (message: string): string => {
    const parsed = parseGraphqlErrorFromJson(message);

    if (parsed === null) {
        return message;
    }

    return getErrorIdentifier(parsed.userCode, parsed.message);
};

export type ShowMessageOptions = {
    toastId?: string;
    action?: ReactNode;
    autoClose?: number | false;
    updateExisting?: boolean;
};

type ToastMessageContentProps = {
    message: string;
    tid: string;
    action?: ReactNode;
};

const ToastMessageContent: FC<ToastMessageContentProps> = ({ message, tid, action }) => (
    <div className={action ? 'custom-toast-content flex min-w-0 flex-1 items-center gap-3' : 'custom-toast-content'}>
        <span className={action ? 'wrap-break-word min-w-0 flex-1' : undefined} data-tid={tid}>
            {message}
        </span>
        {action}
    </div>
);

export const showMessage = (
    message: string,
    type: 'info' | 'error' | 'success',
    options?: ShowMessageOptions,
): void => {
    const toastId = options?.toastId ?? message;

    if (type === 'error') {
        if (isWithToastAndConsoleErrorDebugging) {
            const errorIdentifier = getErrorIdentifierFromMessage(message);

            if (isErrorIgnored(errorIdentifier)) {
                return;
            }

            toast.error(
                () => (
                    <div className="custom-toast-content">
                        <CopyTextBlock
                            textToCopy={message}
                            onIgnore={() => {
                                toast.dismiss(toastId);
                            }}
                        />
                    </div>
                ),
                {
                    toastId,
                    autoClose: false,
                    closeOnClick: false,
                    style: { width: '100%' },
                },
            );
        } else {
            toast.error(() => <ToastMessageContent message={message} tid={TIDs.toast_error} />, {
                toastId,
                closeOnClick: true,
                icon: <RejectedIcon className="p-1" />,
            });
        }
    } else if (type === 'info') {
        toast.info(() => <ToastMessageContent message={message} tid={TIDs.toast_info} />, {
            toastId,
            closeOnClick: true,
            icon: <WarningIcon />,
        });
    } else {
        const content = <ToastMessageContent message={message} tid={TIDs.toast_success} action={options?.action} />;
        const successOptions = {
            toastId,
            closeOnClick: !options?.action,
            icon: <CheckmarkIcon />,
            ...(options?.autoClose !== undefined && { autoClose: options.autoClose }),
        };
        if (options?.updateExisting && toast.isActive(toastId)) {
            toast.update(toastId, { ...successOptions, render: content });
        } else {
            toast.success(content, successOptions);
        }
    }
};
