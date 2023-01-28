import {
    OrderActionButtonBackIconStyled,
    OrderActionButtonNextIconStyled,
    OrderActionLeftStyled,
    OrderActionLinkBackStyled,
    OrderActionRightStyled,
    OrderActionStyled,
} from './OrderAction.style';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC } from 'react';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    buttonBackLink: string;
    buttonNextLink?: string;
    hasDisabledLook: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    nextStepClickHandler?: () => void;
    isLoading?: boolean;
};

const TEST_IDENTIFIER = 'blocks-orderaction-';

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    buttonBackLink,
    buttonNextLink,
    hasDisabledLook,
    nextStepClickHandler,
    withGapBottom,
    withGapTop,
    isLoading,
}) => {
    const router = useRouter();

    const onNextStepHandler = () => {
        if (buttonNextLink !== undefined) {
            router.push(buttonNextLink, undefined);
        }
        if (nextStepClickHandler !== undefined) {
            nextStepClickHandler();
        }
    };

    return (
        <OrderActionStyled withGapBottom={withGapBottom} withGapTop={withGapTop}>
            <OrderActionLeftStyled data-testid={TEST_IDENTIFIER + 'back'}>
                <NextLink href={buttonBackLink} passHref>
                    <OrderActionLinkBackStyled>
                        <OrderActionButtonBackIconStyled alt="" iconType="icon" icon="Arrow" />
                        {buttonBack}
                    </OrderActionLinkBackStyled>
                </NextLink>
            </OrderActionLeftStyled>
            <OrderActionRightStyled data-testid={TEST_IDENTIFIER + 'next'}>
                <Button
                    type="submit"
                    borderRadius="big"
                    variant="primary"
                    hasDisabledLook={hasDisabledLook}
                    onClick={onNextStepHandler}
                >
                    {isLoading && <Loader iconSize={20} />}
                    <span>{buttonNext}</span>
                    <OrderActionButtonNextIconStyled alt="" iconType="icon" icon="Arrow" />
                </Button>
            </OrderActionRightStyled>
        </OrderActionStyled>
    );
};
