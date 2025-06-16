import { Popup } from 'components/Layout/Popup/Popup';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { getGtmShowMessageEvent } from 'gtm/factories/getGtmShowMessageEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import useTranslation from 'next-translate/useTranslation';
import { ReactElement, useEffect, useMemo } from 'react';

type ErrorPopupProps = {
    fields: {
        [fieldName: string]: {
            name: string;
            label: string | ReactElement;
            errorMessage?: string | undefined;
        };
    };
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const ErrorPopup: FC<ErrorPopupProps> = ({ fields, gtmMessageOrigin = GtmMessageOriginType.other }) => {
    const { t } = useTranslation();

    useEffect(() => {
        for (const fieldName in fields) {
            const errorMessage = fields[fieldName].errorMessage;
            if (errorMessage !== undefined) {
                const event = getGtmShowMessageEvent(GtmMessageType.error, errorMessage, fieldName, gtmMessageOrigin);
                gtmSafePushEvent(event);
            }
        }
    }, [fields, gtmMessageOrigin]);

    const mappedErrors = useMemo(() => {
        return Object.entries(fields)
            .filter(([, field]) => field.errorMessage !== undefined)
            .map(([, field]) => (
                <li
                    key={field.name}
                    className="border-border-default mb-2 border-b pb-2 last:mb-0 last:border-none last:pb-0"
                >
                    {field.label}
                    <br />
                    <span className="text-text-error">{field.errorMessage}</span>
                </li>
            ));
    }, [fields]);

    const mappedAriaLabel = useMemo(() => {
        return Object.entries(fields)
            .filter(([, field]) => field.errorMessage !== undefined)
            .map(([, field]) => field.errorMessage)
            .join(', ');
    }, [fields]);

    return (
        <Popup
            ariaDescription={`${t('This form contains validation errors that must be corrected before you can continue.')} ${mappedAriaLabel}`}
            className="w-11/12 max-w-lg"
            contentClassName="overflow-y-auto"
            role="alertdialog"
            title={t('Please check inserted details')}
        >
            <ul className="max-h-[50vh] overflow-y-auto">{mappedErrors}</ul>
        </Popup>
    );
};
