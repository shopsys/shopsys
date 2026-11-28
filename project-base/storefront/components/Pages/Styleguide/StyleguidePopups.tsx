import { Infobox } from 'components/Basic/Infobox/Infobox';
import { Button } from 'components/Forms/Button/Button';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupActions } from 'components/Layout/Popup/PopupActions';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { StyleguideCode, StyleguidePreview, StyleguideRules, StyleguideSpecimen } from './StyleguideElements';

const LONG_CONTENT_PARAGRAPHS = Array.from(
    { length: 12 },
    (_, index) =>
        `Paragraph ${index + 1}. Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempora consectetur at necessitatibus obcaecati optio quas dicta debitis et quod provident harum, voluptatem perferendis ullam soluta temporibus corrupti.`,
);

type StyleguidePopupActionsProps = {
    cancelLabel: string;
    confirmLabel: string;
    confirmVariant?: 'primary' | 'danger';
    onConfirm: () => void;
};

const StyleguidePopupActions: FC<StyleguidePopupActionsProps> = ({
    cancelLabel,
    confirmLabel,
    confirmVariant = 'primary',
    onConfirm,
}) => {
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <PopupActions>
            <Button className="w-full sm:w-auto" variant="outline" onClick={closePortalContent}>
                {cancelLabel}
            </Button>
            <Button className="w-full sm:w-auto" variant={confirmVariant} onClick={onConfirm}>
                {confirmLabel}
            </Button>
        </PopupActions>
    );
};

const ConfirmationPopup: FC = () => {
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const description = 'All products will be removed from your cart. You cannot undo this action.';

    return (
        <Popup ariaDescription={description} role="alertdialog" size="small" title="Remove all products?">
            <div className="flex flex-col gap-6">
                <p>{description}</p>
                <StyleguidePopupActions
                    cancelLabel="Cancel"
                    confirmLabel="Remove all"
                    confirmVariant="danger"
                    onConfirm={closePortalContent}
                />
            </div>
        </Popup>
    );
};

const FormPopup: FC = () => {
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const [email, setEmail] = useState('');

    return (
        <Popup size="small" title="Watch the price">
            <div className="flex flex-col gap-6">
                <p>
                    We will send you an email when the price of this product drops below the current price. You can
                    cancel the watchdog at any time.
                </p>
                <Infobox message="The watchdog is active for 6 months." />
                <TextInput
                    id="styleguide-popup-email"
                    label="Your email"
                    type="email"
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                />
                <StyleguidePopupActions
                    cancelLabel="Cancel"
                    confirmLabel="Watch the price"
                    onConfirm={closePortalContent}
                />
            </div>
        </Popup>
    );
};

const LongContentPopup: FC = () => {
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <Popup contentClassName="overflow-y-auto" size="large" title="Terms and conditions">
            <div className="flex flex-col gap-6">
                <div className="flex flex-col gap-4">
                    {LONG_CONTENT_PARAGRAPHS.map((paragraph) => (
                        <p key={paragraph}>{paragraph}</p>
                    ))}
                </div>
                <StyleguidePopupActions cancelLabel="Close" confirmLabel="I agree" onConfirm={closePortalContent} />
            </div>
        </Popup>
    );
};

export const StyleguidePopups: FC = () => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return (
        <>
            <StyleguideRules
                rules={[
                    <>
                        Actions sit in <StyleguideCode>PopupActions</StyleguideCode> at the end of the content, aligned
                        right: the secondary action (Cancel) first, the main action last.
                    </>,
                    'On mobile the actions stack and take the full width.',
                    'A confirmation of a destructive action uses role="alertdialog", the small size and the danger variant.',
                    <>
                        A form in a popup uses <StyleguideCode>{'<FormBlockWrapper variant="plain">'}</StyleguideCode>{' '}
                        without the gray surface and the small size, so its fields align with the title and the actions.
                    </>,
                    <>
                        The popup is at most 80 % of the viewport high. Long content needs{' '}
                        <StyleguideCode>contentClassName="overflow-y-auto"</StyleguideCode> to scroll inside it.
                    </>,
                ]}
            />

            <StyleguidePreview className="flex flex-wrap items-start gap-x-10 gap-y-6">
                <StyleguideSpecimen hint='size="small", role="alertdialog"' label="Confirmation">
                    <Button variant="outline" onClick={() => updatePortalContent(<ConfirmationPopup />)}>
                        Open confirmation
                    </Button>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint='size="small"' label="Content with a form">
                    <Button variant="outline" onClick={() => updatePortalContent(<FormPopup />)}>
                        Open form popup
                    </Button>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint='size="large", scrolling content' label="Long content">
                    <Button variant="outline" onClick={() => updatePortalContent(<LongContentPopup />)}>
                        Open long popup
                    </Button>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </>
    );
};
