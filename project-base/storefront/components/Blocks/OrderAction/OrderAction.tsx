import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    hasDisabledLook: boolean;
    backStepClickHandler?: () => void;
    nextStepClickHandler?: () => void;
    shouldShowSpinnerOnNextStepButton?: boolean;
    ariaLabelNextStep?: string;
};

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    hasDisabledLook,
    backStepClickHandler,
    nextStepClickHandler,
    shouldShowSpinnerOnNextStepButton,
    ariaLabelNextStep,
}) => {
    const { t } = useTranslation();

    return (
        <div className="my-5 flex flex-col-reverse items-center justify-between gap-4 md:my-10 md:flex-row">
            <Button
                aria-label={t('Go to previous step')}
                size="large"
                tid={TIDs.blocks_orderaction_back}
                variant="inverted"
                onClick={backStepClickHandler}
            >
                <ArrowSecondaryIcon className="size-3 rotate-90" />
                {buttonBack}
            </Button>

            <SubmitButton
                aria-label={ariaLabelNextStep}
                isWithDisabledLook={hasDisabledLook}
                size="xlarge"
                tid={TIDs.blocks_orderaction_next}
                onClick={nextStepClickHandler}
            >
                {shouldShowSpinnerOnNextStepButton && <SpinnerIcon className="size-4" />}

                <span>{buttonNext}</span>

                <ArrowSecondaryIcon className="size-4 -rotate-90" />
            </SubmitButton>
        </div>
    );
};
