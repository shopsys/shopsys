import { FC, MouseEventHandler } from 'react';
import {
    PromoCodeInfoCouponIconStyled,
    PromoCodeInfoCouponStyled,
    PromoCodeInfoStyled,
    PromoCodeInfoTitleStyled,
} from './PromoCodeInfo.style';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type PromoCodeInfoProps = {
    discount: string;
    setIsPromoCodeInfo: MouseEventHandler<HTMLDivElement>;
};

const PromoCodeInfo: FC<PromoCodeInfoProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <PromoCodeInfoStyled>
            <PromoCodeInfoTitleStyled>{t('Your discount with the code has been applied.')}</PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled>
                {props.discount}
                <PromoCodeInfoCouponIconStyled icon="Cross" onClick={props.setIsPromoCodeInfo} />
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
