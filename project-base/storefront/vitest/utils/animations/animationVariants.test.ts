import { fadeAnimation } from 'utils/animations/animationVariants';
import { describe, expect, test } from 'vitest';

describe('fadeAnimation', () => {
    test('uses the valid CSS opacity range', () => {
        expect(fadeAnimation.visible).toEqual({ opacity: 1 });
        expect(fadeAnimation.hidden).toEqual({ opacity: 0 });
    });
});
