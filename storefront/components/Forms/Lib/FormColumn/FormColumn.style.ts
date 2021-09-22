import { FormLineStyled } from 'components/Forms/Lib/FormLine/FormLine.style';
import { styled } from 'components/Theme/main';

const localVariables = {
    formColumnGap: '12px',
};

export const FormColumnStyled = styled.div`
    display: flex;
    flex-wrap: wrap;
    margin-left: -${localVariables.formColumnGap};

    ${FormLineStyled} {
        padding-left: ${localVariables.formColumnGap};
    }
`;
