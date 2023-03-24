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

let clickableElementsSiblings = document.querySelectorAll('.clickableSibling');

for (let i=0; i<clickableElementsSiblings.length; i++) {
    clickableElementsSiblings[i].addEventListener('click', function (e) {
        let nextElementSibling = this.nextElementSibling;
        if (nextElementSibling.style.display === "none") {
            nextElementSibling.style.display = "block";
        } else {
            nextElementSibling.style.display = "none";
        }
    });
}

let clickableBox = document.querySelectorAll('.clickableBox');

for (let i=0; i<clickableBox.length; i++) {
    clickableBox[i].addEventListener('click', function (e) {
        let box = this;
        if (box.checked) {
            box.nextElementSibling.style.display = "block";
        } else {
            box.nextElementSibling.style.display = "none";
        }
    });
}