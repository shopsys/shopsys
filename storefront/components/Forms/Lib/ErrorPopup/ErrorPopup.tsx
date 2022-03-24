import { ErrorListItemStyled, ErrorListStyled, ErrorMessageStyled, ErrorPopupStyled } from './ErrorPopup.style';
import { FC, ReactElement } from 'react';
import Heading from 'components/Basic/Heading';
import Popup from 'components/Layout/Popup';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

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
};

const ErrorPopup: FC<ErrorPopupProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <Popup wrapperComponent={ErrorPopupStyled} isVisible={props.isVisible} onCloseCallback={props.onCloseCallback}>
            <Heading type="h2">{t('Please, check inserted details.')}</Heading>
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
