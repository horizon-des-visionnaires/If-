const draggable = document.getElementById("Message");

let isDragging = false, offsetX = 0, offsetY = 0;

draggable.addEventListener('mousedown', (e) => {
    e.preventDefault();
    isDragging = true;
    offsetX = e.clientX - draggable.offsetLeft;
    offsetY = e.clientY - draggable.offsetTop;
    document.addEventListener('mousemove', moveElement);
    document.addEventListener('mouseup', stopMoveElement);
});

function moveElement(e) {
    if (!isDragging) return;

    e.preventDefault();

    // Calculate new position
    let newX = e.clientX - offsetX;
    let newY = e.clientY - offsetY;

    // Ensure the element stays within the viewport
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    const elementWidth = draggable.offsetWidth;
    const elementHeight = draggable.offsetHeight;

    if (newX < 0) newX = 0;
    if (newY < 0) newY = 0;
    if (newX + elementWidth > windowWidth) newX = windowWidth - elementWidth;
    if (newY + elementHeight > windowHeight) newY = windowHeight - elementHeight;

    draggable.style.left = newX + "px";
    draggable.style.top = newY + "px";
}

function stopMoveElement() {
    if (!isDragging) return;

    isDragging = false;
    document.removeEventListener('mousemove', moveElement);
    document.removeEventListener('mouseup', stopMoveElement);

    // Get window dimensions
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // Get element dimensions and position
    const elementRect = draggable.getBoundingClientRect();
    const elementWidth = elementRect.width;
    const elementHeight = elementRect.height;
    const elementLeft = elementRect.left;
    const elementTop = elementRect.top;

    // Calculate the distances to the nearest edges
    const distanceToLeft = elementLeft;
    const distanceToRight = windowWidth - (elementLeft + elementWidth);
    const distanceToTop = elementTop;
    const distanceToBottom = windowHeight - (elementTop + elementHeight);

    // Find the nearest edge
    const minDistance = Math.min(distanceToLeft, distanceToRight, distanceToTop, distanceToBottom);

    if (minDistance === distanceToLeft) {
        draggable.style.left = "0px";
    } else if (minDistance === distanceToRight) {
        draggable.style.left = (windowWidth - elementWidth) + "px";
    } else if (minDistance === distanceToTop) {
        draggable.style.top = "0px";
    } else if (minDistance === distanceToBottom) {
        draggable.style.top = (windowHeight - elementHeight) + "px";
    }
}
