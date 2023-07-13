import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

const TEST_IDENTIFIER = 'blocks-ordersteps-';

export const OrderSteps: FC<OrderStepsProps> = ({ activeStep, domainUrl }) => {
    const t = useTypedTranslationFunction();
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        domainUrl,
    );

    return (
        <Webline>
            <ul className="-mr-5 mb-6 flex justify-between border-b border-greyLighter p-0 lg:mb-3">
                <OrderStepsListItem dataTestId={TEST_IDENTIFIER + '1'}>
                    {activeStep > 1 ? (
                        <OrderStepsListItemLink isActive={false} isClickable href={cartUrl}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLink>
                    ) : (
                        <OrderStepsListItemLink isActive={activeStep === 1}>{'1. ' + t('Cart')}</OrderStepsListItemLink>
                    )}
                </OrderStepsListItem>
                <OrderStepsListItem dataTestId={TEST_IDENTIFIER + '2'}>
                    {activeStep > 2 ? (
                        <OrderStepsListItemLink isActive={false} isClickable href={transportAndPaymentUrl}>
                            {'2. ' + t('Transport and payment')}
                        </OrderStepsListItemLink>
                    ) : (
                        <OrderStepsListItemLink isActive={activeStep === 2}>
                            {'2. ' + t('Transport and payment')}
                        </OrderStepsListItemLink>
                    )}
                </OrderStepsListItem>
                <OrderStepsListItem dataTestId={TEST_IDENTIFIER + '3'}>
                    <OrderStepsListItemLink isActive={activeStep === 3}>
                        {'3. ' + t('Contact information')}
                    </OrderStepsListItemLink>
                </OrderStepsListItem>
            </ul>
        </Webline>
    );
};

const OrderStepsListItem: FC = ({ children, dataTestId }) => (
    <li className="relative w-1/3 p-3 lg:py-3 lg:px-5" data-testid={dataTestId}>
        {children}
    </li>
);

type OrderStepsListItemLinkProps = { isActive: boolean; isClickable?: boolean; href?: string };

const OrderStepsListItemLink: FC<OrderStepsListItemLinkProps> = ({ children, isActive, isClickable, href }) => {
    const Component = (
        <span
            className={twJoin(
                'block text-xs uppercase no-underline',
                isClickable && 'cursor-pointer hover:text-primary hover:no-underline hover:outline-none',
                isActive &&
                    'text-primary before:absolute before:bottom-0 before:left-0 before:right-0 before:h-[2px] before:bg-primary before:content-[""]',
            )}
        >
            {children}
        </span>
    );

    return href ? (
        <ExtendedNextLink href={href} type="static">
            {Component}
        </ExtendedNextLink>
    ) : (
        Component
    );
};
