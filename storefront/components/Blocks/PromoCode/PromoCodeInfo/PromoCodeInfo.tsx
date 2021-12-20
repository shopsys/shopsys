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
    const testIdentifier = 'blocks-promocode-promocodeinfo';

    const t = useTypedTranslationFunction();

    return (
        <PromoCodeInfoStyled data-testid={testIdentifier}>
            <PromoCodeInfoTitleStyled data-testid={testIdentifier + '-title'}>
                {t('Your discount with the code has been applied.')}
            </PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled data-testid={testIdentifier + '-code'}>
                {props.promoCode}
                <PromoCodeInfoCouponIconStyled iconType="icon" icon="Cross" onClick={props.onRemovePromoCodeCallback} />
            </PromoCodeInfoCouponStyled>
            <p data-testid={testIdentifier + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </PromoCodeInfoStyled>
    );
};

export default PromoCodeInfo;
