import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    hasDisabledCursor?: boolean;
    hasDisabledLook?: boolean;
    isDisabled?: boolean;
    backStepClickHandler?: () => void;
    nextStepClickHandler?: () => void;
    shouldShowSpinnerOnNextStepButton?: boolean;
    ariaLabelNextStep?: string;
};

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    isDisabled,
    hasDisabledCursor,
    hasDisabledLook,
    backStepClickHandler,
    nextStepClickHandler,
    shouldShowSpinnerOnNextStepButton,
    ariaLabelNextStep,
}) => {
    const { t } = useTranslation();

    return (
        <div className="my-5 flex flex-wrap-reverse items-center justify-between gap-4 md:my-10">
            <Button
                aria-label={t('Go to previous step', { ns: 'accessibility' })}
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
                disabled={isDisabled}
                hasDisabledCursor={hasDisabledCursor}
                hasDisabledLook={isDisabled || hasDisabledLook}
                shouldShowSpinner={shouldShowSpinnerOnNextStepButton}
                size="xlarge"
                tid={TIDs.blocks_orderaction_next}
                onClick={nextStepClickHandler}
            >
                <span>{buttonNext}</span>

                <ArrowSecondaryIcon className="size-4 -rotate-90" />
            </SubmitButton>
        </div>
    );
};
