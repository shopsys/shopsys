import { ErrorListItemStyled, ErrorListStyled, ErrorMessageStyled } from './ErrorPopup.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { getGtmMessageEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, ReactElement, useEffect, useMemo } from 'react';
import { GtmMessageOriginType } from 'types/gtm';

type ErrorPopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    fields: {
        [fieldName: string]: {
            name: string;
            label: string | ReactElement;
            errorMessage?: string | undefined;
        };
    };
    origin: GtmMessageOriginType;
};

export const ErrorPopup: FC<ErrorPopupProps> = ({ isVisible, onCloseCallback, fields, origin }) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (isVisible) {
            for (const fieldName in fields) {
                const errorMessage = fields[fieldName].errorMessage;
                if (errorMessage !== undefined) {
                    const event = getGtmMessageEvent('error', errorMessage, fieldName, origin);
                    gtmSafePushEvent(event);
                }
            }
        }
    }, [isVisible, fields, origin]);

    const mappedErrors = useMemo(() => {
        const newMappedErrors = [];
        for (const field in fields) {
            if (fields[field].errorMessage === undefined) {
                continue;
            }

            newMappedErrors.push(
                <ErrorListItemStyled key={fields[field].name}>
                    {fields[field].label}
                    <br />
                    <ErrorMessageStyled>{fields[field].errorMessage}</ErrorMessageStyled>
                </ErrorListItemStyled>,
            );
        }

        return newMappedErrors;
    }, [fields]);

    return (
        <Popup
            wrapperComponent={PopupStyled}
            isVisible={isVisible}
            onCloseCallback={onCloseCallback}
            className="w-11/12 max-w-lg"
        >
            <Heading type="h2">{t('Please check inserted details')}</Heading>
            <ErrorListStyled>{mappedErrors}</ErrorListStyled>
        </Popup>
    );
};
