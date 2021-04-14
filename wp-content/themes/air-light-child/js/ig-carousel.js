const slideshow = document.querySelector(".slide-wrap");

if (slideshow != null) {
  //make sure we don't run this script if the slideshow is not present

  let slides = document.querySelectorAll(".slide-entry"),
    slideCount = slides.length,
    currentSlide = 0,
    slideHeight = null,
    initialHeight = slides[0].clientHeight;
  0;

  console.log("active?");

  slides[0].classList.add("active"); //on load, activate the first slide

  function moveToSlide(n) {
    // set up our slide navigation functionality
    slides[currentSlide].className = "slide-entry";
    currentSlide = (n + slideCount) % slideCount;
    slides[currentSlide].className = "slide-entry active";
    slideHeight = slides[currentSlide].clientHeight;
    slideshow.style.height = slideHeight + "px";
    window.addEventListener("resize", function () {
      resizedSlideHeight = slides[currentSlide].clientHeight;
      slideshow.style.height = resizedSlideHeight + "px";
    });
  }

  function nextSlide(e) {
    moveToSlide(currentSlide + 1);
    let code = e.keyCode;
    if (code == 39) {
      moveToSlide(currentSlide + 1);
    }
  }
  function prevSlide(e) {
    moveToSlide(currentSlide + -1);
    let code = e.keyCode;
    if (code == 37) {
      moveToSlide(currentSlide + -1);
    }
  }

  const slideHandlers = {
    nextSlide: function (element) {
      document.querySelector(element).addEventListener("click", nextSlide);
      document.body.addEventListener("keydown", nextSlide, false);
    },
    prevSlide: function (element) {
      document.querySelector(element).addEventListener("click", prevSlide);
      document.body.addEventListener("keydown", prevSlide, false);
    },
  };

  slideHandlers.nextSlide("#next-slide");
  slideHandlers.prevSlide("#prev-slide");

  // Dynamic slideshow height

  slideshow.style.height = initialHeight + "px"; //on load, set the height of the slider to the first active slide

  window.addEventListener("resize", function () {
    // adjust the height of the slidehow as the browser is resized
    let resizedHeight = slides[0].clientHeight;
    slideshow.style.height = resizedHeight + "px";
  });

  // Detect swipe events for touch devices, credit to Kirupa @ https://www.kirupa.com/html5/detecting_touch_swipe_gestures.htm
  let initialX = null;
  let initialY = null;
  function startTouch(e) {
    initialX = e.touches[0].clientX;
    initialY = e.touches[0].clientY;
  }
  function moveTouch(e) {
    if (initialX === null) {
      return;
    }
    if (initialY === null) {
      return;
    }
    let currentX = e.touches[0].clientX;
    let currentY = e.touches[0].clientY;
    let diffX = initialX - currentX;
    let diffY = initialY - currentY;
    if (Math.abs(diffX) > Math.abs(diffY)) {
      if (diffX > 0) {
        // swiped left
        moveToSlide(currentSlide + 1);
      } else {
        // swiped right
        moveToSlide(currentSlide + -1);
      }
    }
    initialX = null;
    initialY = null;
    e.preventDefault();
  }

  slideshow.addEventListener("touchstart", startTouch, false);
  slideshow.addEventListener("touchmove", moveTouch, false);

  // optional autoplay function
  setInterval(function () {
    nextSlide();
  }, 5000);
} //end slideshow
