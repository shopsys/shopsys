import { StyleguideSection } from './StyleguideElements';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { useSessionStore } from 'store/useSessionStore';

export const StyleguidePopups: FC = () => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return (
        <StyleguideSection title="Popups">
            <Button
                onClick={() => {
                    updatePortalContent(
                        <Popup>
                            <div className="p-12">Example popup</div>
                        </Popup>,
                    );
                }}
            >
                Open Popup
            </Button>
        </StyleguideSection>
    );
};
