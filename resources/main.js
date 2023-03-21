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

// function changeVisibilityOfSibling(elemId) {
//     let elem = document.getElementById(elemId);
//     let elemSibling = elem.nextSibling;
//     if (elem.checked) {
//         elemSibling.style.display = "block";
//     } else {
//         elemSibling.style.display = "none";
//     }
// }

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