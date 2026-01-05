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

export const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    const { restoreStoredFocus } = useSessionStore.getState();

    if (type === 'error') {
        if (isWithToastAndConsoleErrorDebugging) {
            const errorIdentifier = getErrorIdentifierFromMessage(message);

            if (isErrorIgnored(errorIdentifier)) {
                // eslint-disable-next-line no-console
                console.debug(`[Ignored Error] ${errorIdentifier}:`, message);
                return;
            }

            const toastId = message;

            toast.error(
                () => (
                    <CopyTextBlock
                        textToCopy={message}
                        onIgnore={() => {
                            toast.dismiss(toastId);
                        }}
                    />
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
            toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_error} />, {
                toastId: message,
                closeOnClick: true,
                icon: <CloseIcon className="p-1" />,
                onClose: () => restoreStoredFocus(),
            });
        }
    } else if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_info} />, {
            toastId: message,
            closeOnClick: true,
            icon: <InfoInTriangleIcon />,
            onClose: () => restoreStoredFocus(),
        });
    } else {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_success} />, {
            toastId: message,
            closeOnClick: true,
            icon: <CheckmarkIcon />,
            onClose: () => restoreStoredFocus(),
        });
    }
};
