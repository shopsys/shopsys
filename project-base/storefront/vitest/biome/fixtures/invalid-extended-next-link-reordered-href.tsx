import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

export const InvalidExtendedNextLinkReorderedHrefFixture = () => (
    <ExtendedNextLink onClick={() => undefined} href="#">
        Open popup
    </ExtendedNextLink>
);
