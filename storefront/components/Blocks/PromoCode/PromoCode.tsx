import { FC, useRef, useState } from 'react';
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
import PromoCodeInfo from './PromoCodeInfo';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const PromoCode: FC = () => {
    const t = useTypedTranslationFunction();
    const [isPromoCodeInfo, setIsPromoCodeInfo] = useState(false);
    const [discount, setDiscount] = useState('TestCode');
    const [isContentVisible, setIsContentVisible] = useState(false);
    const contentElement = useRef<HTMLDivElement>(null);
    const nodeRef = useRef(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    return (
        <PromoCodeStyled contentElementHeight={contentElementHeight}>
            {isPromoCodeInfo ? (
                <PromoCodeInfo discount={discount} setIsPromoCodeInfo={() => setIsPromoCodeInfo(!isPromoCodeInfo)} />
            ) : (
                <>
                    <PromoCodeButtonStyled onClick={() => setIsContentVisible(!isContentVisible)}>
                        <PromoCodeButtonIconStyled icon="Plus" />
                        {t('I have a discount coupon')}
                    </PromoCodeButtonStyled>
                    <CSSTransition
                        in={isContentVisible}
                        timeout={300}
                        classNames="promoCode"
                        onEnter={calcHeight}
                        onExit={calcHeight}
                        unmountOnExit
                        nodeRef={nodeRef}
                    >
                        <PromoCodeContentWrapperStyled ref={nodeRef}>
                            <PromoCodeContentStyled ref={contentElement}>
                                <PromoCodeContentInputStyled
                                    type="text"
                                    id="promoCode"
                                    name="promoCode"
                                    label={t('Coupon')}
                                    style={{ width: '100%', marginBottom: '0' }}
                                />
                                <PromoCodeContentButtonStyled
                                    type="submit"
                                    onClick={() => setIsPromoCodeInfo(!isPromoCodeInfo)}
                                >
                                    {t('Apply')}
                                </PromoCodeContentButtonStyled>
                            </PromoCodeContentStyled>
                        </PromoCodeContentWrapperStyled>
                    </CSSTransition>
                </>
            )}
        </PromoCodeStyled>
    );
};

export default PromoCode;
