let clickableElements = document.querySelectorAll('.clickable');

for (let i=0; i<clickableElements.length; i++) {
    clickableElements[i].addEventListener('click', function (e) {
        let lastChild = this.lastChild;
        if (lastChild.style.display === "none") {
            lastChild.style.display = "block";
        } else {
            lastChild.style.display = "none";
        }
    });
}