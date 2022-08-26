import { usePromoCodeForm, usePromoCodeFormMeta } from './formMeta';
import {
    PromoCodeButtonIconStyled,
    PromoCodeButtonStyled,
    PromoCodeContentButtonStyled,
    PromoCodeContentInputStyled,
    PromoCodeContentStyled,
    PromoCodeContentWrapperStyled,
    PromoCodeStyled,
} from './PromoCode.style';
import { PromoCodeInfo } from './PromoCodeInfo/PromoCodeInfo';
import { Form } from 'components/Forms/Form/Form';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useApplyPromoCodeToCart } from 'hooks/cart/UseApplyPromoCodeToCart';
import { useRemovePromoCodeFromCart } from 'hooks/cart/UseRemovePromoCodeFromCart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useEffect, useRef, useState } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import { PromoCodeFormType } from 'types/form';

const TEST_IDENTIFIER = 'blocks-promocode';

export const PromoCode: FC = () => {
    const { promoCode } = useCurrentCart();
    const t = useTypedTranslationFunction();
    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [formProviderMethods] = usePromoCodeForm();
    const { setValue } = formProviderMethods;
    const formMeta = usePromoCodeFormMeta(formProviderMethods);
    const applyPromoCode = useApplyPromoCodeToCart();
    const removePromoCode = useRemovePromoCodeFromCart();

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    const onApplyPromoCodeHandler: SubmitHandler<PromoCodeFormType> = async (data, event) => {
        event?.preventDefault();
        applyPromoCode(data.promoCode, formMeta.messages.addPromoCode);
    };

    const onRemovePromoCodeHandler = async (promoCode: string) => {
        removePromoCode(promoCode, formMeta.messages.removePromoCode);
    };

    useEffect(() => {
        if (promoCode === null) {
            setValue('promoCode', '');
        }
    }, [promoCode, setValue]);

    return (
        <PromoCodeStyled contentElementHeight={contentElementHeight} data-testid={TEST_IDENTIFIER}>
            {promoCode !== null ? (
                <PromoCodeInfo promoCode={promoCode} onRemovePromoCodeCallback={onRemovePromoCodeHandler} />
            ) : (
                <>
                    <PromoCodeButtonStyled
                        onClick={() => setIsContentVisible(!isContentVisible)}
                        data-testid={TEST_IDENTIFIER + '-add-button'}
                    >
                        <PromoCodeButtonIconStyled alt="" iconType="icon" icon="Plus" />
                        {t('I have a discount coupon')}
                    </PromoCodeButtonStyled>
                    <CSSTransition
                        in={isContentVisible}
                        timeout={300}
                        classNames="promoCode"
                        onEnter={calcHeight}
                        onExit={calcHeight}
                        unmountOnExit
                        nodeRef={cssTransitionRef}
                    >
                        <PromoCodeContentWrapperStyled ref={cssTransitionRef}>
                            <PromoCodeContentStyled ref={contentElement}>
                                <FormProvider {...formProviderMethods}>
                                    <Form
                                        onSubmit={formProviderMethods.handleSubmit(onApplyPromoCodeHandler)}
                                        style={{ display: 'flex' }}
                                    >
                                        <Controller
                                            name={formMeta.fields.promoCode.name}
                                            control={formProviderMethods.control}
                                            render={({ field }) => (
                                                <>
                                                    <PromoCodeContentInputStyled
                                                        type="text"
                                                        id={formMeta.formName + '-' + formMeta.fields.promoCode.name}
                                                        label={formMeta.fields.promoCode.label}
                                                        fieldRef={field}
                                                        style={{ width: '100%', marginBottom: '0' }}
                                                    />
                                                    <PromoCodeContentButtonStyled
                                                        type="submit"
                                                        isDisabled={!formProviderMethods.formState.isValid}
                                                        data-testid={TEST_IDENTIFIER + '-apply-button'}
                                                    >
                                                        {t('Apply')}
                                                    </PromoCodeContentButtonStyled>
                                                </>
                                            )}
                                        />
                                    </Form>
                                </FormProvider>
                            </PromoCodeContentStyled>
                        </PromoCodeContentWrapperStyled>
                    </CSSTransition>
                </>
            )}
        </PromoCodeStyled>
    );
};
