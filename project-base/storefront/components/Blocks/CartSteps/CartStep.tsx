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
    const isDisabled = (activeStep === 1 && step === 3 && !isClickable) || activeStep === step;

    return (
        <li>
            <button
                disabled={isDisabled}
                className={twJoin(
                    'group flex max-w-[70px] flex-col items-center gap-2.5 outline-none md:max-w-none lg:flex-row lg:gap-5',
                    isDisabled && 'cursor-default',
                )}
                onClick={() => onClickHandler(step, url, pageType)(activeStep)}
            >
                <div
                    className={twJoin(
                        'flex size-11 items-center justify-center rounded-full',
                        step === activeStep
                            ? 'bg-textAccent text-textInverted'
                            : 'bg-backgroundAccentLess text-textAccent',
                    )}
                >
                    <h4>{step}</h4>
                </div>

                <h4
                    className={twJoin(
                        'text-xs lg:text-lg',
                        !isDisabled && 'group-hover:text-linkHovered',
                        step === activeStep ? 'text-link' : '',
                    )}
                >
                    {label}
                </h4>
            </button>
        </li>
    );
};
