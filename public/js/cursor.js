function setCursorWait() {
    const style = document.createElement('style');
    style.id = 'global-wait-cursor';
    style.innerHTML = `
* {
    cursor: progress !important;
}
`;
    document.head.appendChild(style);
}

function resetCursor() {
    const style = document.getElementById('global-wait-cursor');
    if (style) style.remove();
}