import {
    OrderActionButtonBackIconStyled,
    OrderActionButtonNextIconStyled,
    OrderActionLeftStyled,
    OrderActionLinkBackStyled,
    OrderActionRightStyled,
    OrderActionStyled,
} from './OrderAction.style';
import { Button } from 'components/Forms/Button/Button';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC } from 'react';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    buttonBackLink: string;
    buttonNextLink?: string;
    activeStep: number;
    hasDisabledLook: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    nextStepClickHandler?: () => void;
};

export const OrderAction: FC<OrderActionProps> = (props) => {
    const testIdentifier = 'blocks-orderaction-';

    const router = useRouter();

    const onNextStepHandler = () => {
        if (props.buttonNextLink !== undefined) {
            router.push(props.buttonNextLink, undefined, { shallow: true });
        }
        if (props.nextStepClickHandler !== undefined) {
            props.nextStepClickHandler();
        }
    };

    return (
        <OrderActionStyled withGapBottom={props.withGapBottom} withGapTop={props.withGapTop}>
            <OrderActionLeftStyled data-testid={testIdentifier + 'back'}>
                <NextLink href={props.buttonBackLink} passHref shallow prefetch>
                    <OrderActionLinkBackStyled>
                        <OrderActionButtonBackIconStyled iconType="icon" icon="Arrow" />
                        {props.buttonBack}
                    </OrderActionLinkBackStyled>
                </NextLink>
            </OrderActionLeftStyled>
            <OrderActionRightStyled data-testid={testIdentifier + 'next'}>
                <Button
                    type="submit"
                    borderRadius="big"
                    variant="primary"
                    hasDisabledLook={props.hasDisabledLook}
                    onClick={onNextStepHandler}
                >
                    {props.buttonNext}
                    <OrderActionButtonNextIconStyled iconType="icon" icon="Arrow" />
                </Button>
            </OrderActionRightStyled>
        </OrderActionStyled>
    );
};
