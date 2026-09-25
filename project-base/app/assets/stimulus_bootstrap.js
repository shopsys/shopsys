import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import { startStimulusApp } from '@symfony/reprise/stimulus';

// registers controllers.json (UX packages) and local controllers from assets/controllers
export const app = startStimulusApp();

app.load(
    definitionsFromContext(
        import.meta.webpackContext('@shopsys/administration/src/controllers', {
            recursive: true,
            regExp: /\.[jt]sx?$/,
        }),
    ),
);
