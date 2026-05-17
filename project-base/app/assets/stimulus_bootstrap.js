import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import { startStimulusApp } from '@symfony/stimulus-bridge';

export const app = startStimulusApp(
    require.context('@symfony/stimulus-bridge/lazy-controller-loader!./controllers', true, /\.[jt]sx?$/),
);

app.load(
    definitionsFromContext(
        require.context(
            '@symfony/stimulus-bridge/lazy-controller-loader!@shopsys/administration/src/controllers',
            true,
            /\.[jt]sx?$/,
        ),
    ),
);
