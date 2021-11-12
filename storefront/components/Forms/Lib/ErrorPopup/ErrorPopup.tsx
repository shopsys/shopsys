import { ErrorListItemStyled, ErrorListStyled, ErrorMessageStyled, ErrorPopupStyled } from './ErrorPopup.style';
import { FC, ReactElement } from 'react';
import Heading from 'components/Basic/Heading';
import Popup from 'components/Layout/Popup';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ErrorPopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    errors: { label: string | ReactElement; message: string | undefined }[];
};

const ErrorPopup: FC<ErrorPopupProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <Popup wrapperComponent={ErrorPopupStyled} isVisible={props.isVisible} onCloseCallback={props.onCloseCallback}>
            <Heading type="h2">{t('Please, check inserted details.')}</Heading>
            <ErrorListStyled>
                {props.errors.map((error) =>
                    error.message === undefined ? null : (
                        <ErrorListItemStyled key={error.label.toString()}>
                            {error.label}
                            <br />
                            <ErrorMessageStyled>{error.message}</ErrorMessageStyled>
                        </ErrorListItemStyled>
                    ),
                )}
            </ErrorListStyled>
        </Popup>
    );
};

export default ErrorPopup;
