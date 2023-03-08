import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type PromoCodeStyledProps = {
    contentElementHeight: number;
};

export const PromoCodeStyled = styled.div<PromoCodeStyledProps>(
    ({ theme, contentElementHeight }) => css`
        position: relative;

        @media ${theme.mediaQueries.queryVl} {
            width: 300px;
        }
        .promoCode-enter {
            height: 0;
        }

        .promoCode-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.3s all ease;
        }

        .promoCode-exit {
            height: ${contentElementHeight}px;
        }

        .promoCode-exit-active {
            height: 0;
            transition: 0.3s all ease;
        }
    `,
);
