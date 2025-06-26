'use client';

import { CartSteps } from 'components/Blocks/CartSteps/CartSteps';
import { OrderSummary } from 'components/Blocks/OrderSummary/OrderSummary';
import { useAppConfig } from 'components/providers/AppConfigProvider';

type OrderContentWrapperProps = {
    activeStep: number;
    isTransportOrPaymentLoading?: boolean;
};

export const OrderContentWrapper: FC<OrderContentWrapperProps> = ({
    activeStep,
    isTransportOrPaymentLoading,
    children,
}) => {
    const { url } = useAppConfig((appConfig) => appConfig.domainConfig);

    return (
        <>
            <CartSteps activeStep={activeStep} domainUrl={url} />

            <section className="vl:grid-cols-3 vl:gap-10 grid">
                <div className="vl:col-span-2">{children}</div>

                <OrderSummary isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
            </section>
        </>
    );
};
