import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import {
    PromoCodeButtonIconStyled,
    PromoCodeButtonStyled,
    PromoCodeContentButtonStyled,
    PromoCodeContentInputStyled,
    PromoCodeContentStyled,
    PromoCodeContentWrapperStyled,
    PromoCodeStyled,
} from './PromoCode.style';
import { usePromoCodeForm, usePromoCodeFormMeta } from './formMeta';
import { CSSTransition } from 'react-transition-group';
import Form from 'components/Forms/Form';
import { PromoCodeFormType } from 'types/form';
import PromoCodeInfo from './PromoCodeInfo';
import { useApplyPromoCodeToCart } from 'hooks/cart/UseApplyPromoCodeToCart';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useRemovePromoCodeFromCart } from 'hooks/cart/UseRemovePromoCodeFromCart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const PromoCode: FC = () => {
    const testIdentifier = 'blocks-promocode';
    const { promoCode } = useCurrentCart();
    const t = useTypedTranslationFunction();
    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [formProviderMethods] = usePromoCodeForm();
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
            formProviderMethods.setValue('promoCode', '');
        }
    }, [promoCode]);

    return (
        <PromoCodeStyled contentElementHeight={contentElementHeight} data-testid={testIdentifier}>
            {promoCode !== null ? (
                <PromoCodeInfo promoCode={promoCode} onRemovePromoCodeCallback={onRemovePromoCodeHandler} />
            ) : (
                <>
                    <PromoCodeButtonStyled
                        onClick={() => setIsContentVisible(!isContentVisible)}
                        data-testid={testIdentifier + '-add-button'}
                    >
                        <PromoCodeButtonIconStyled iconType="icon" icon="Plus" />
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
                                        noValidate
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
                                                        data-testid={testIdentifier + '-apply-button'}
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

export default PromoCode;
