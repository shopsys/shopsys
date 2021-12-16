import { styled } from 'components/Theme/main';

const localVariables = {
    transportImageMaxHeight: '20px',
    transportImageMaxWidth: '35px',
} as const;

export const TransportImageWrapperStyled = styled.div`
    position: relative;
    display: inline;
    top: 4px;
    margin-right: 5px;
    max-width: ${localVariables.transportImageMaxWidth};

    img {
        max-width: ${localVariables.transportImageMaxWidth};
        max-height: ${localVariables.transportImageMaxHeight};
    }
`;
