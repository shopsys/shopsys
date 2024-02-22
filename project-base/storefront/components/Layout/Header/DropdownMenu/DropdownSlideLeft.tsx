import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import useTranslation from 'next-translate/useTranslation';
import { DropdownListLevels } from 'types/dropdown';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

export const DropdownSlideLeft: FC<DropdownSlideLeftProps> = ({ goToMenu, onClickEvent }) => {
    const { t } = useTranslation();

    return (
        <span
            className="relative -top-6 ml-8 inline-flex cursor-pointer items-center text-xs uppercase text-dark"
            onClick={() => onClickEvent({ goToMenu })}
        >
            <ArrowIcon className="mr-2 rotate-90" />
            {t('Back')}
        </span>
    );
};
