import { ErrorListItemStyled, ErrorListStyled, ErrorMessageStyled, ErrorPopupStyled } from './ErrorPopup.style';
import Heading from 'components/Basic/Heading';
import Popup from 'components/Layout/Popup';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, ReactElement, useEffect } from 'react';
import { GtmMessageOriginType } from 'types/gtm';
import { getGtmMessageEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

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

const ErrorPopup: FC<ErrorPopupProps> = (props) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (props.isVisible) {
            for (const fieldName in props.fields) {
                const errorMessage = props.fields[fieldName].errorMessage;
                if (errorMessage !== undefined) {
                    const event = getGtmMessageEvent('error', errorMessage, fieldName, props.origin);
                    gtmSafePushEvent(event);
                }
            }
        }
    }, [props.isVisible, props.fields, props.origin]);

    return (
        <Popup wrapperComponent={ErrorPopupStyled} isVisible={props.isVisible} onCloseCallback={props.onCloseCallback}>
            <Heading type="h2">{t('Please check inserted details')}</Heading>
            <ErrorListStyled>
                {(() => {
                    const mappedErrors = [];
                    for (const field in props.fields) {
                        if (props.fields[field].errorMessage === undefined) {
                            continue;
                        }

                        mappedErrors.push(
                            <ErrorListItemStyled key={props.fields[field].name}>
                                {props.fields[field].label}
                                <br />
                                <ErrorMessageStyled>{props.fields[field].errorMessage}</ErrorMessageStyled>
                            </ErrorListItemStyled>,
                        );
                    }
                    return mappedErrors;
                })()}
            </ErrorListStyled>
        </Popup>
    );
};

export default ErrorPopup;
