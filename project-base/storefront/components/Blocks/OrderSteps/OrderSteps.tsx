import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

export const OrderSteps: FC<OrderStepsProps> = ({ activeStep, domainUrl }) => {
    const { t } = useTranslation();
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        domainUrl,
    );

    return (
        <ul className="mb-6 flex justify-between border-b border-borderAccentLess p-0 lg:mb-3">
            <OrderStepsListItem>
                {activeStep > 1 ? (
                    <OrderStepsListItemLink isClickable href={cartUrl} isActive={false}>
                        {'1. ' + t('Shopping cart')}
                    </OrderStepsListItemLink>
                ) : (
                    <OrderStepsListItemLink isActive={activeStep === 1}>
                        {'1. ' + t('Shopping cart')}
                    </OrderStepsListItemLink>
                )}
            </OrderStepsListItem>
            <OrderStepsListItem>
                {activeStep > 2 ? (
                    <OrderStepsListItemLink isClickable href={transportAndPaymentUrl} isActive={false}>
                        {'2. ' + t('Transport and payment')}
                    </OrderStepsListItemLink>
                ) : (
                    <OrderStepsListItemLink isActive={activeStep === 2}>
                        {'2. ' + t('Transport and payment')}
                    </OrderStepsListItemLink>
                )}
            </OrderStepsListItem>
            <OrderStepsListItem>
                <OrderStepsListItemLink isActive={activeStep === 3}>
                    {'3. ' + t('Contact information')}
                </OrderStepsListItemLink>
            </OrderStepsListItem>
        </ul>
    );
};

const OrderStepsListItem: FC = ({ children }) => <li className="relative w-1/3 p-3 lg:py-3 lg:px-5">{children}</li>;

type OrderStepsListItemLinkProps = { isActive: boolean; isClickable?: boolean; href?: string };

const OrderStepsListItemLink: FC<OrderStepsListItemLinkProps> = ({ children, isActive, isClickable, href }) => {
    let linkClassName = 'text-linkDisabled';
    if (isClickable) {
        linkClassName = 'cursor-pointer hover:text-linkHovered';
    } else if (isActive) {
        linkClassName =
            'text-linkHovered before:absolute before:bottom-0 before:left-0 before:right-0 before:h-[2px] before:bg-linkHovered before:content-[""]';
    }
    const Component = (
        <span
            className={twJoin(
                'block text-xs uppercase no-underline hover:no-underline hover:outline-none',
                linkClassName,
            )}
        >
            {children}
        </span>
    );

    return href ? (
        <ExtendedNextLink className="!no-underline" href={href}>
            {Component}
        </ExtendedNextLink>
    ) : (
        Component
    );
};
