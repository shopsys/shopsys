import useTranslation from 'next-translate/useTranslation';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
import { useCartStepNavigation } from 'utils/cart/useCartStepNavigation';

type CartStepProps = {
    step: number;
    activeStep: number;
    url: string;
    pageType: PageType;
    label: string;
    onClickHandler: ReturnType<typeof useCartStepNavigation>['handleStepClick'];
    isClickable?: boolean;
};

export const CartStep: FC<CartStepProps> = ({
    step,
    activeStep,
    url,
    pageType,
    label,
    onClickHandler,
    isClickable,
}) => {
    const { t } = useTranslation();
    const isDisabled = (activeStep === 1 && step === 3 && !isClickable) || activeStep === step;

    return (
        <li>
            <button
                aria-label={t('Go to {{ step }} step', { step: step })}
                disabled={isDisabled}
                tabIndex={0}
                className={twJoin(
                    'group flex max-w-[70px] flex-col items-center gap-2.5 outline-hidden md:max-w-none lg:flex-row lg:gap-5',
                    'rounded-md',
                    isDisabled ? 'cursor-default' : 'cursor-pointer',
                )}
                onClick={() => onClickHandler(step, url, pageType)(activeStep)}
            >
                <span
                    className={twJoin(
                        'flex size-11 items-center justify-center rounded-full',
                        step === activeStep
                            ? 'bg-text-accent text-text-inverted'
                            : 'bg-background-accent-less text-text-accent',
                    )}
                >
                    <span className="h4">{step}</span>
                </span>

                <span
                    className={twJoin(
                        'font-secondary text-xs font-semibold lg:text-lg',
                        !isDisabled && 'group-hover:text-link-hovered',
                        step === activeStep ? 'text-link-default' : '',
                    )}
                >
                    {label}
                </span>
            </button>
        </li>
    );
};
