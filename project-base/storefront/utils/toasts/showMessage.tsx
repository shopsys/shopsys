import { CopyTextBlock } from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { InfoInTriangleIcon } from 'components/Basic/Icon/InfoInTriangleIcon';
import { TIDs } from 'cypress/tids';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
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

type ShowMessageOptions = {
    toastId?: string;
};

type ToastMessageContentProps = {
    message: string;
    tid: string;
};

const ToastMessageContent: FC<ToastMessageContentProps> = ({ message, tid }) => (
    <div className="custom-toast-content">
        <span dangerouslySetInnerHTML={{ __html: message }} data-tid={tid} />
    </div>
);

export const showMessage = (
    message: string,
    type: 'info' | 'error' | 'success',
    options?: ShowMessageOptions,
): void => {
    const { restoreStoredFocus } = useSessionStore.getState();
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
                    onClose: () => restoreStoredFocus(),
                },
            );
        } else {
            toast.error(() => <ToastMessageContent message={message} tid={TIDs.toast_error} />, {
                toastId,
                closeOnClick: true,
                icon: <CloseIcon className="p-1" />,
                onClose: () => restoreStoredFocus(),
            });
        }
    } else if (type === 'info') {
        toast.info(() => <ToastMessageContent message={message} tid={TIDs.toast_info} />, {
            toastId,
            closeOnClick: true,
            icon: <InfoInTriangleIcon />,
            onClose: () => restoreStoredFocus(),
        });
    } else {
        toast.success(() => <ToastMessageContent message={message} tid={TIDs.toast_success} />, {
            toastId,
            closeOnClick: true,
            icon: <CheckmarkIcon />,
            onClose: () => restoreStoredFocus(),
        });
    }
};
