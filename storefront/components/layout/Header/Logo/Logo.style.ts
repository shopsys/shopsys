import Image from 'next/image';
import styled from 'styled-components';
import { Theme } from 'theme/main';

export const LogoStyled = styled(Image)`
    ${({ theme }: { theme: Theme }) => `
        display: flex;
        width: 100%;
        max-width: 120px;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 163px;
        }
    `}
`;
