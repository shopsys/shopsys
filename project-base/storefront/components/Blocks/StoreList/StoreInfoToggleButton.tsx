import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

type StoreInfoToggleButtonProps = {
    store: StoreOrPacketeryPoint;
    storeInfoId: string;
    isExpanded: boolean;
    onClick: () => void;
};

export const StoreInfoToggleButton: FC<StoreInfoToggleButtonProps> = ({ store, storeInfoId, isExpanded, onClick }) => {
    const { t } = useTranslation();

    return (
        <button
            aria-controls={storeInfoId}
            aria-expanded={isExpanded}
            aria-label={
                isExpanded
                    ? t('Collapse store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
                    : t('Expand store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
            }
            className={twMergeCustom(
                'flex cursor-pointer items-center rounded-md p-0.5 text-icon-default outline-hidden',
                !isExpanded && '-my-2.5 -mr-5 self-stretch rounded-none py-3 pr-5',
            )}
            title={isExpanded ? t('Collapse store info') : t('Expand store info')}
            type="button"
            onClick={(event) => {
                event.stopPropagation();
                onClick();
            }}
        >
            <ArrowIcon aria-hidden="true" className={`size-5 transition ${isExpanded ? 'rotate-180' : ''}`} />
        </button>
    );
};
