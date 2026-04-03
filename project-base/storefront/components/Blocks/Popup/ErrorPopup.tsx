import { Popup } from 'components/Layout/Popup/Popup';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { getGtmShowMessageEvent } from 'gtm/factories/getGtmShowMessageEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { ReactElement, useEffect } from 'react';
import { FieldErrors } from 'react-hook-form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ErrorPopupProps = {
    fields: {
        [fieldName: string]: {
            name: string;
            label: string | ReactElement;
            errorMessage?: string | undefined;
        };
    };
    errors?: FieldErrors;
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const getErrorMessage = (
    fieldName: string,
    field: { errorMessage?: string | undefined },
    errors?: FieldErrors,
): string | undefined => {
    if (errors) {
        const message = errors[fieldName]?.message;

        return typeof message === 'string' ? message : undefined;
    }

    return field.errorMessage;
};

export const ErrorPopup: FC<ErrorPopupProps> = ({ fields, errors, gtmMessageOrigin = GtmMessageOriginType.other }) => {
    const { t } = useTranslation();

    const fieldsWithErrors = Object.entries(fields).filter(
        ([fieldName, field]) => getErrorMessage(fieldName, field, errors) !== undefined,
    );

    useEffect(() => {
        const entries = Object.entries(fields).filter(
            ([fieldName, field]) => getErrorMessage(fieldName, field, errors) !== undefined,
        );
        for (const [fieldName, field] of entries) {
            const errorMessage = getErrorMessage(fieldName, field, errors);
            if (errorMessage !== undefined) {
                const event = getGtmShowMessageEvent(GtmMessageType.error, errorMessage, fieldName, gtmMessageOrigin);
                gtmSafePushEvent(event);
            }
        }
    }, [fields, errors, gtmMessageOrigin]);

    const mappedErrors = fieldsWithErrors.map(([fieldName, field]) => (
        <li key={field.name} className="mb-2 border-border-default border-b pb-2 last:mb-0 last:border-none last:pb-0">
            {field.label}
            <br />
            <span className="text-text-error">{getErrorMessage(fieldName, field, errors)}</span>
        </li>
    ));

    const mappedAriaLabel = fieldsWithErrors
        .map(([fieldName, field]) => getErrorMessage(fieldName, field, errors))
        .filter((msg): msg is string => typeof msg === 'string')
        .join(', ');

    return (
        <Popup
            className="w-11/12 max-w-lg"
            contentClassName="overflow-y-auto"
            role="alertdialog"
            title={t('Please check inserted details')}
            ariaDescription={`${t(
                'This form contains validation errors that must be corrected before you can continue.',
                {
                    ns: 'accessibility',
                },
            )} ${mappedAriaLabel}`}
        >
            <ul className="max-h-[50vh] overflow-y-auto">{mappedErrors}</ul>
        </Popup>
    );
};
