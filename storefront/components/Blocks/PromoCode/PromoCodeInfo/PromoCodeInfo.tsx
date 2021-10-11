import { FC, MouseEventHandler } from 'react';
import {
    PromoCodeInfoCouponIconStyled,
    PromoCodeInfoCouponStyled,
    PromoCodeInfoStyled,
    PromoCodeInfoTitleStyled,
} from './PromoCodeInfo.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: MouseEventHandler<HTMLDivElement>;
};

const PromoCodeInfo: FC<PromoCodeInfoProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <PromoCodeInfoStyled>
            <PromoCodeInfoTitleStyled>{t('Your discount with the code has been applied.')}</PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled>
                {props.promoCode}
                <PromoCodeInfoCouponIconStyled icon="Cross" onClick={props.onRemovePromoCodeCallback} />
            </PromoCodeInfoCouponStyled>
            <p>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </PromoCodeInfoStyled>
    );
};

export default PromoCodeInfo;
