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
        const newMappedErrors = [];
        for (const field in fields) {
            if (fields[field].errorMessage === undefined) {
                continue;
            }

            newMappedErrors.push(
                <li key={fields[field].name} className="mb-2 border-b border-graySlate pb-2">
                    {fields[field].label}
                    <br />
                    <span className="text-red">{fields[field].errorMessage}</span>
                </li>,
            );
        }

        return newMappedErrors;
    }, [fields]);

    return (
        <Popup className="w-11/12 max-w-lg">
            <div className="h2 mb-3">{t('Please check inserted details')}</div>
            <ul className="max-h-[50vh] overflow-y-auto">{mappedErrors}</ul>
        </Popup>
    );
};
