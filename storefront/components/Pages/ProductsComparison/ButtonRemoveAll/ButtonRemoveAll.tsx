import { ButtonRemoveAllIconStyled, ButtonRemoveAllStyled, ButtonRemoveAllTextStyled } from './ButtonRemoveAll.style';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type ButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ButtonRemoveAll: FC<ButtonRemoveAllProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { handleRemoveAllFromComparison } = useHandleCompare('');

    return (
        <ButtonRemoveAllStyled
            className={props.displayMobile ? 'displayOnMobile' : undefined}
            onClick={handleRemoveAllFromComparison}
        >
            <ButtonRemoveAllTextStyled>{t('Delete all')}</ButtonRemoveAllTextStyled>
            <ButtonRemoveAllIconStyled iconType="icon" icon="RemoveThin" />
        </ButtonRemoveAllStyled>
    );
};
