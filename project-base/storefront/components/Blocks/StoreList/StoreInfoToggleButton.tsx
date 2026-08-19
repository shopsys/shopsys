import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoreInfoToggleButtonProps = {
    store: StoreOrPacketeryPoint;
    storeInfoId: string;
    isExpanded: boolean;
    onClick: () => void;
};

export const StoreInfoToggleButton: FC<StoreInfoToggleButtonProps> = ({ store, storeInfoId, isExpanded, onClick }) => {
    const { t } = useTranslation();

    return (
        <IconButton
            Icon={ArrowIcon}
            aria-controls={storeInfoId}
            aria-expanded={isExpanded}
            ariaLabel={
                isExpanded
                    ? t('Collapse store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
                    : t('Expand store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
            }
            iconClassName={isExpanded ? 'rotate-180' : undefined}
            shape="rounded"
            size="small"
            title={isExpanded ? t('Collapse store info') : t('Expand store info')}
            variant="ghost"
            onClick={(event) => {
                event.stopPropagation();
                onClick();
            }}
        />
    );
};
