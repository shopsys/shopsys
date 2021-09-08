import { DropdownListLevels } from '../types';
import { DropdownSlideLeftStyled } from './DropdownSlideLeft.style';
import { FC } from 'react';
import ShopsysIcon from '../../../../basic/ShopsysIcon';
import { useTranslation } from 'react-i18next';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

const DropdownSlideLeft: FC<DropdownSlideLeftProps> = (props) => {
    const { t } = useTranslation();

    return (
        <DropdownSlideLeftStyled onClick={() => props.onClickEvent(props)}>
            <ShopsysIcon iconHeight={14} icon="arrow-black" />
            {t('Back')}
        </DropdownSlideLeftStyled>
    );
};

/* @component */
export default DropdownSlideLeft;
