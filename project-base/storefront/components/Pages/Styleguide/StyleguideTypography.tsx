import { StyleguideSection } from './StyleguideElements';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import React from 'react';

export const StyleguideTypography: FC = () => {
    return (
        <StyleguideSection className="flex flex-wrap flex-col gap-3" title="Typography">
            <h1>H1 title</h1>
            <div className="h1">H1 className</div>
            <h2>H2 title</h2>
            <div className="h2">H2 className</div>
            <h3>H3 title</h3>
            <div className="h3">H3 className</div>
            <h4>H4 title</h4>
            <div className="h4">H4 className</div>
            <h5>H5 title</h5>
            <div className="h5">H5 className</div>

            <p>
                Example paragraph. Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempora consectetur at
                necessitatibus obcaecati optio quas dicta debitis et quod provident harum, voluptatem perferendis ullam
                soluta temporibus corrupti, alias velit nobis.
            </p>

            <a>Simple anchor link</a>
            <ExtendedNextLink href="#">ExtendedNextLink</ExtendedNextLink>
        </StyleguideSection>
    );
};
