import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Infobox } from 'components/Basic/Infobox/Infobox';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StoreContact } from './StoreContact';

type StoreExpandedInfoProps = {
    store: StoreOrPacketeryPoint;
    isSelected: boolean;
    keyName: string;
    shouldShowSelectButton: boolean;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreExpandedInfo: FC<StoreExpandedInfoProps> = ({
    store,
    isSelected,
    keyName,
    shouldShowSelectButton,
    onSelectStoreCallback,
}) => {
    const { t } = useTranslation();

    return (
        <AnimateCollapseDiv className="block! pt-5" keyName={keyName}>
            <div className="flex flex-col items-start gap-5">
                {!!store.specialMessage && <Infobox message={store.specialMessage} />}

                {store.description && <p className="text-sm" dangerouslySetInnerHTML={{ __html: store.description }} />}

                {store.phone || store.email ? <StoreContact email={store.email} phone={store.phone} /> : null}

                <div className="flex flex-col gap-2">
                    <p className="font-secondary font-semibold text-text-default">
                        {t('Opening hours', { ns: 'accessibility' })}
                    </p>

                    <OpeningHours openingHours={store.openingHours} />
                </div>

                <div className="flex flex-wrap gap-2.5">
                    {shouldShowSelectButton && onSelectStoreCallback !== undefined && (
                        <Button
                            hasDisabledLook={isSelected}
                            size="small"
                            tid={TIDs.store_select_button}
                            variant={isSelected ? 'inverted' : 'primary'}
                            onClick={(event) => {
                                event.stopPropagation();
                                onSelectStoreCallback(store.identifier);
                            }}
                            onKeyDown={(event) => event.stopPropagation()}
                        >
                            {isSelected ? t('Selected store') : t('Select store')}
                        </Button>
                    )}

                    <LinkButton
                        aria-label={t('Store detail for {{storeName}}', {
                            ns: 'accessibility',
                            storeName: store.name,
                        })}
                        href={store.slug}
                        size="small"
                        tid={TIDs.store_detail_link}
                        type="store"
                        variant="secondary"
                    >
                        {t('Store detail')}
                    </LinkButton>
                </div>
            </div>
        </AnimateCollapseDiv>
    );
};
