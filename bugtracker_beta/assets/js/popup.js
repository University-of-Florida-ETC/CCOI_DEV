const blur = document.getElementById('blurOverlay');
const popup = document.getElementById('popup');

function showPopup() {
    blur.classList.toggle('blurred');
    popup.classList.toggle('popped');
}

function closePopup() {
    if (blur.classList.contains('blurred')) {
        blur.classList.toggle('blurred');
        const currentPopup = document.getElementsByClassName("popped")[0];
        currentPopup.classList.toggle("popped");
    }
}