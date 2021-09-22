import Image from 'next/image';
import { styled } from 'components/Theme/main';

export const LogoStyled = styled(Image)`
    ${({ theme }) => `
        display: flex;
        width: 100%;
        max-width: 120px;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 163px;
        }
    `}
`;
