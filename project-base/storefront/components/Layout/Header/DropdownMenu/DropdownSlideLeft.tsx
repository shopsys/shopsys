import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { DropdownListLevels } from 'types/dropdown';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-slideleft';

export const DropdownSlideLeft: FC<DropdownSlideLeftProps> = ({ goToMenu, onClickEvent }) => {
    const t = useTypedTranslationFunction();

    return (
        <span
            className="relative -top-6 ml-8 inline-flex cursor-pointer items-center text-xs uppercase text-dark"
            onClick={() => onClickEvent({ goToMenu })}
            data-testid={TEST_IDENTIFIER}
        >
            <Icon iconType="icon" icon="Arrow" className="mr-2 rotate-90" />
            {t('Back')}
        </span>
    );
};
