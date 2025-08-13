import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

export const navigateWithTab = async (options?: { shift?: boolean }, steps: number = 1) => {
    const user = userEvent.setup();
    for (let i = 0; i < steps; i++) {
        await user.tab(options);
    }
};

export const navigateWithArrows = async (direction: 'up' | 'down' | 'left' | 'right', steps: number = 1) => {
    const user = userEvent.setup();
    const keyMap = {
        up: '{ArrowUp}',
        down: '{ArrowDown}',
        left: '{ArrowLeft}',
        right: '{ArrowRight}',
    };

    for (let i = 0; i < steps; i++) {
        await user.keyboard(keyMap[direction]);
    }
};

export const pressEnterKey = async () => {
    const user = userEvent.setup();
    await user.keyboard('{Enter}');
};

export const pressEscapeKey = async () => {
    const user = userEvent.setup();
    await user.keyboard('{Escape}');
};

export const pressSpaceKey = async () => {
    const user = userEvent.setup();
    await user.keyboard(' ');
};

export const pressHomeKey = async () => {
    const user = userEvent.setup();
    await user.keyboard('{Home}');
};

export const pressEndKey = async () => {
    const user = userEvent.setup();
    await user.keyboard('{End}');
};

export const navigateToElement = async (role: string, name: string) => {
    const element = screen.getByRole(role, { name });
    element.focus();
    return element;
};
