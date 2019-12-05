export default function pushReloadState (url, title, stateObject) {
    const currentState = history.state || {};
    if (!currentState.hasOwnProperty('refreshOnPopstate') || currentState.refreshOnPopstate !== true) {
        currentState.refreshOnPopstate = true;
        history.replaceState(currentState, document.title, location.href);
    }

    if (title === undefined) {
        title = '';
    }

    if (stateObject === undefined) {
        stateObject = {};
    }
    stateObject.refreshOnPopstate = true;

    history.pushState(stateObject, title, url);
};
