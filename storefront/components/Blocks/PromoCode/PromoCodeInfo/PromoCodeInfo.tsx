import {
    PromoCodeInfoCouponIconStyled,
    PromoCodeInfoCouponStyled,
    PromoCodeInfoStyled,
    PromoCodeInfoTitleStyled,
} from './PromoCodeInfo.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: (promoCode: string) => void;
};

export const PromoCodeInfo: FC<PromoCodeInfoProps> = (props) => {
    const testIdentifier = 'blocks-promocode-promocodeinfo';

    const t = useTypedTranslationFunction();

    const onRemovePromoCodeHandler = () => {
        props.onRemovePromoCodeCallback(props.promoCode);
    };

    return (
        <PromoCodeInfoStyled data-testid={testIdentifier}>
            <PromoCodeInfoTitleStyled data-testid={testIdentifier + '-title'}>
                {t('Your discount with the code has been applied.')}
            </PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled data-testid={testIdentifier + '-code'}>
                {props.promoCode}
                <PromoCodeInfoCouponIconStyled iconType="icon" icon="Cross" onClick={onRemovePromoCodeHandler} />
            </PromoCodeInfoCouponStyled>
            <p data-testid={testIdentifier + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </PromoCodeInfoStyled>
    );
};
