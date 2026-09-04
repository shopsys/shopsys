import { UserText } from 'components/Basic/UserText/UserText';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';

type AdditionalServiceDescriptionPopupProps = {
    name: string;
    description: string;
    onClose: () => void;
};

export const AdditionalServiceDescriptionPopup: FC<AdditionalServiceDescriptionPopupProps> = ({
    name,
    description,
    onClose,
}) => {
    return (
        <Popup className="w-11/12 max-w-2xl" contentClassName="overflow-y-auto" title={name} onClose={onClose}>
            <div data-tid={TIDs.additional_service_description_popup}>
                <UserText htmlContent={description} />
            </div>
        </Popup>
    );
};
