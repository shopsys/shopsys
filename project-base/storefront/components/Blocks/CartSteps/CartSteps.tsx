import { CartStep } from './CartStep';
import { CartStepSeparator } from './CartStepSeparator';
import useTranslation from 'next-translate/useTranslation';
import { useCartStepNavigation } from 'utils/cart/useCartStepNavigation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type CartStepsProps = {
    activeStep: number;
    domainUrl: string;
};

export const CartSteps: FC<CartStepsProps> = ({ activeStep, domainUrl }) => {
    const { t } = useTranslation();
    const { handleStepClick, isSecondStepFilled } = useCartStepNavigation();
    const [cartUrl, transportAndPaymentUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment', '/order/contact-information'],
        domainUrl,
    );

    return (
        <nav aria-label={t('Checkout steps navigation')}>
            <ul className="mx-auto flex max-w-[840px] items-baseline justify-between gap-2.5 px-2.5 pt-1 pb-5 md:px-0 lg:items-center xl:gap-5 xl:pt-6 xl:pb-10">
                <CartStep
                    activeStep={activeStep}
                    label={t('Shopping cart')}
                    pageType="cart"
                    step={1}
                    url={cartUrl}
                    onClickHandler={handleStepClick}
                />

                <CartStepSeparator />

                <CartStep
                    activeStep={activeStep}
                    label={t('Transport and payment')}
                    pageType="transport-and-payment"
                    step={2}
                    url={transportAndPaymentUrl}
                    onClickHandler={handleStepClick}
                />

                <CartStepSeparator />

                <CartStep
                    activeStep={activeStep}
                    isClickable={isSecondStepFilled}
                    label={t('Contact information')}
                    pageType="contact-information"
                    step={3}
                    url={contactInformationUrl}
                    onClickHandler={handleStepClick}
                />
            </ul>
        </nav>
    );
};
