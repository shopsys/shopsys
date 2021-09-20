import { FC, useState } from 'react';
import {
    PromoCodeButtonIconStyled,
    PromoCodeButtonStyled,
    PromoCodeContentButtonStyled,
    PromoCodeContentInputStyled,
    PromoCodeContentStyled,
    PromoCodeContentWrapperStyled,
    PromoCodeStyled,
} from './PromoCode.style';
import { CSSTransition } from 'react-transition-group';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const OrderSteps: FC = () => {
    const t = useTypedTranslationFunction();
    const [isContentVisible, setIsContentVisible] = useState(false);

    return (
        <PromoCodeStyled>
            <PromoCodeButtonStyled onClick={() => setIsContentVisible(!isContentVisible)}>
                <PromoCodeButtonIconStyled icon="Plus" />
                {t('I have a discount coupon')}
            </PromoCodeButtonStyled>
            <CSSTransition in={isContentVisible} timeout={300} classNames="promoCode" unmountOnExit>
                <PromoCodeContentWrapperStyled>
                    <PromoCodeContentStyled>
                        <PromoCodeContentInputStyled
                            type="text"
                            id="promoCode"
                            name="promoCode"
                            label={t('Coupon')}
                            style={{ width: '100%', marginBottom: '0' }}
                        />
                        <PromoCodeContentButtonStyled type="submit">{t('Apply')}</PromoCodeContentButtonStyled>
                    </PromoCodeContentStyled>
                </PromoCodeContentWrapperStyled>
            </CSSTransition>
        </PromoCodeStyled>
    );
};

export default OrderSteps;
