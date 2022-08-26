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

const TEST_IDENTIFIER = 'blocks-promocode-promocodeinfo';

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const t = useTypedTranslationFunction();

    const onRemovePromoCodeHandler = () => {
        onRemovePromoCodeCallback(promoCode);
    };

    return (
        <PromoCodeInfoStyled data-testid={TEST_IDENTIFIER}>
            <PromoCodeInfoTitleStyled data-testid={TEST_IDENTIFIER + '-title'}>
                {t('Your discount with the code has been applied.')}
            </PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled data-testid={TEST_IDENTIFIER + '-code'}>
                {promoCode}
                <PromoCodeInfoCouponIconStyled alt="" iconType="icon" icon="Cross" onClick={onRemovePromoCodeHandler} />
            </PromoCodeInfoCouponStyled>
            <p data-testid={TEST_IDENTIFIER + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </PromoCodeInfoStyled>
    );
};
