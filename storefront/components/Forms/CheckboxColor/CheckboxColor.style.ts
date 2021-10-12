import { styled } from 'components/Theme/main';

export const ChoiceFormLineStyled = styled.div`
    width: fit-content;
    margin-bottom: 16px;
    margin-right: 5px;
`;

export const CheckboxColorStyled = styled.input`
    position: absolute;
    height: 1px;
    width: 1px;
    margin: -1px;
    padding: 0;
    z-index: -1000;
    overflow: hidden;

    clip: rect(0 0 0 0);
    border: 0;
`;
