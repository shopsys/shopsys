import { StyleguideSection } from './StyleguideElements';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import React, { useState } from 'react';

export const StyleguidePopups: FC = () => {
    const [isPopupOpen, setIsPopupOpen] = useState(false);

    return (
        <StyleguideSection title="Popups">
            <Button
                onClick={() => {
                    setIsPopupOpen(true);
                }}
            >
                Open Popup
            </Button>

            {isPopupOpen && (
                <Popup onCloseCallback={() => setIsPopupOpen(false)}>
                    <div className="p-12">Example popup</div>
                </Popup>
            )}
        </StyleguideSection>
    );
};
