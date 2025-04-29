document.querySelectorAll(".accordion-title").forEach((button) => {
  button.addEventListener("click", () => {
    const item = button.parentElement;
    item.classList.toggle("active");
  });
});
// Testimonial Slider
const slider = document.querySelector(".testimonial-slider");
const prevBtn = document.querySelector(".prev-btn");
const nextBtn = document.querySelector(".next-btn");

let scrollIndex = 0;
const cardWidth = 320; // card + gap (adjust if needed)

prevBtn.addEventListener("click", () => {
  scrollIndex = Math.max(scrollIndex - 1, 0);
  slider.style.transform = `translateX(-${scrollIndex * cardWidth}px)`;
});

nextBtn.addEventListener("click", () => {
  const maxIndex = slider.children.length - 1;
  scrollIndex = Math.min(scrollIndex + 1, maxIndex);
  slider.style.transform = `translateX(-${scrollIndex * cardWidth}px)`;
});

// Smooth scrolling for tab buttons
document.querySelectorAll(".tab-btn").forEach((button) => {
  button.addEventListener("click", () => {
    const targetId = button.getAttribute("data-target");
    const targetElement = document.querySelector(targetId);
    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: "smooth",
        block: "start",
      }); // Align to the top
    }
  });
});


