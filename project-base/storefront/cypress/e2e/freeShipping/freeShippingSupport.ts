import { t } from 'support/translations';
import { TIDs } from 'tids';

export const checkFreeTransportBannerShowsRemaining = () => {
    t('Buy for {{ amount }} and get free shipping!').then((translatedText) => {
        const expectedPrefix = translatedText.split('{{')[0].trim();
        cy.getByTID([TIDs.free_transport_range]).should('be.visible').and('contain.text', expectedPrefix);
    });
};

export const checkFreeTransportBannerShowsFree = () => {
    t('Your delivery and payment is now free of charge!').then((translatedText) => {
        cy.getByTID([TIDs.free_transport_range]).should('be.visible').and('contain.text', translatedText);
    });
};
