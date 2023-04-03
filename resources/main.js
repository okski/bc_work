let clickableElements = document.querySelectorAll('.clickable');

for (let i=0; i<clickableElements.length; i++) {
    clickableElements[i].addEventListener('click', function () {
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
    clickableElementsSiblings[i].addEventListener('click', function () {
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
    clickableBox[i].addEventListener('click', function () {
        let box = this;
        if (box.checked) {
            box.nextElementSibling.style.display = "block";
        } else {
            box.nextElementSibling.style.display = "none";
        }
    });
}

// document.forms.addHomeworkForm.addEventListener('submit', function (e) {
//     checkMarking(e, this);
// });
if (typeof document.forms.homeworkForm !== 'undefined') {
    document.forms.homeworkForm.addEventListener('submit', function (e) {
        checkMarking(e, this);
    });
}


function checkMarking(e, markingDiv) {
    <!--Marking regular expression: ^{\s*\"maximum\":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)+(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$-->
    const pattern = /^{\s*"maximum":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)*(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$/;
    let marking = markingDiv['Marking'];
    if (marking.nextElementSibling.style.display === 'block') {
        marking.nextElementSibling.style.display = 'none';
    }
    try {
        JSON.parse(marking.value);
    } catch (error) {
        e.preventDefault();
        marking.nextElementSibling.innerHTML = 'Invalid JSON format.';
        marking.nextElementSibling.style.display = 'block';
        return;
    }

    if (!pattern.test(marking.value)) {
        e.preventDefault();
        marking.nextElementSibling.innerHTML = 'Does not match wanted JSON structure.';
        marking.nextElementSibling.style.display = 'block';
    }
}

function startTimer(duration, display) {
    let timer = duration, minutes, seconds;
    setInterval(function () {
        seconds = parseInt(timer, 10);

        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = seconds;

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}

if (document.getElementById('refresh') !== null) {
    window.onload = function () {
        let thirtySeconds = 29,
            display = document.querySelector('#refresh').firstElementChild;
        startTimer(thirtySeconds, display);
    };
}
