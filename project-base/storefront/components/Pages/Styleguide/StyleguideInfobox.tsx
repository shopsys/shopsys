import { Infobox } from 'components/Basic/Infobox/Infobox';
import { StyleguideSection } from './StyleguideElements';

export const StyleguideInfobox: FC = () => {
    return (
        <StyleguideSection title="Infobox">
            <Infobox message="Example message" />
        </StyleguideSection>
    );
};
