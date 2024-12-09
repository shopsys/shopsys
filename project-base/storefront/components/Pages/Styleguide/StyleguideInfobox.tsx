import { StyleguideSection } from './StyleguideElements';
import { Infobox } from 'components/Basic/Infobox/Infobox';

export const StyleguideInfobox: FC = () => {
    return (
        <StyleguideSection title="Infobox">
            <Infobox message="Example message" />
        </StyleguideSection>
    );
};
