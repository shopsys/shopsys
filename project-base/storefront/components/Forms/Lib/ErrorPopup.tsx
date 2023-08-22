import { Heading } from 'components/Basic/Heading/Heading';
import { Popup } from 'components/Layout/Popup/Popup';
import { getGtmShowMessageEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import useTranslation from 'next-translate/useTranslation';
import { ReactElement, useEffect, useMemo } from 'react';
import { GtmMessageOriginType, GtmMessageType } from 'gtm/types/enums';

type ErrorPopupProps = {
    onCloseCallback: () => void;
    fields: {
        [fieldName: string]: {
            name: string;
            label: string | ReactElement;
            errorMessage?: string | undefined;
        };
    };
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const ErrorPopup: FC<ErrorPopupProps> = ({
    onCloseCallback,
    fields,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
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
                <li className="mb-2 border-b border-greyLighter pb-2" key={fields[field].name}>
                    {fields[field].label}
                    <br />
                    <span className="text-red">{fields[field].errorMessage}</span>
                </li>,
            );
        }

        return newMappedErrors;
    }, [fields]);

    return (
        <Popup onCloseCallback={onCloseCallback} className="w-11/12 max-w-lg">
            <Heading type="h2">{t('Please check inserted details')}</Heading>
            <ul className="max-h-[50vh] overflow-y-auto">{mappedErrors}</ul>
        </Popup>
    );
};
