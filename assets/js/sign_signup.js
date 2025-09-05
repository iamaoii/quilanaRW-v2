const inputs = document.querySelectorAll(".input-field");
const toggle_btn = document.querySelectorAll(".toggle");
const main = document.querySelector("main");
const bullets = document.querySelectorAll(".bullets span");
const images = document.querySelectorAll(".image");

inputs.forEach((inp) => {
    inp.addEventListener("focus", () => {
        inp.classList.add("active");
    });

    inp.addEventListener("blur", () => {
        if (inp.value != "") return;
        inp.classList.remove("active");
    });
});

toggle_btn.forEach((btn) => {
    btn.addEventListener("click", () => {
        main.classList.toggle("sign-up-mode");
    });
});

let currentIndex = 1; // Start from the first image
let autoSlideInterval;

// Function to move the slider
function moveSlider(index) {
    if (index < 1) index = images.length;
    if (index > images.length) index = 1;

    // Show the current image and update the text slider
    images.forEach((img) => img.classList.remove("show"));
    document.querySelector(`.img-${index}`).classList.add("show");

    const textSlider = document.querySelector(".text-group");
    textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

    // Update bullets' active state
    bullets.forEach((bull) => bull.classList.remove("active"));
    bullets[index - 1].classList.add("active");

    // Update the currentIndex
    currentIndex = index;
}

// Start automatic sliding
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        moveSlider(currentIndex + 1);
    }, 10000);
}

// Stop automatic sliding
function stopAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

// Initial setup
startAutoSlide();

// Attach click event to bullets
bullets.forEach((bullet) => {
    bullet.addEventListener("click", function() {
        const index = parseInt(this.dataset.value);
        moveSlider(index);
        stopAutoSlide(); // Stop auto sliding on user interaction
    });
});

// Move slider initially
moveSlider(currentIndex);

let userType = '';

$(document).ready(function(){
    $('#signin-form').submit(function(e){
        e.preventDefault();
        $('#signin-form input[type="submit"]').attr('disabled', true).val('Please wait...');

        $.ajax({
            url: 'login_auth.php',
            method: 'POST',
            data: $(this).serialize(),
            error: function(err) {
                console.log(err);
                alert('An error occurred');
                $('#signin-form input[type="submit"]').removeAttr('disabled').val('Sign In');
            },
            success: function(resp) {
                if (resp == 1) {
                    var userType = $('#user_type').val();
                    if (userType == '2') {
                        location.replace('faculty_dashboard.php');
                    } else {
                        location.replace('student_dashboard.php');
                    }
                } else {
                    alert("Incorrect username or password.");
                    $('#signin-form input[type="submit"]').removeAttr('disabled').val('Sign In');
                }
            }
        });
    });
});