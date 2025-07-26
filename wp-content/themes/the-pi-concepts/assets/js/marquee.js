document.addEventListener("DOMContentLoaded", function () {
  const marqueeContainer = document.querySelector(".marquee-container");
  const marqueeText = marqueeContainer.querySelector("p");

  // Get initial widths
  const marqueeContainerWidth = marqueeContainer.offsetWidth;
  const marqueeTextWidth = marqueeText.offsetWidth;
  let totalTextWidth = marqueeTextWidth;

  // Keep appending clones of the text until the total width overflows the container
  while (totalTextWidth < marqueeContainerWidth) {
    marqueeContainer.appendChild(marqueeText.cloneNode(true));
    totalTextWidth += marqueeTextWidth;
  }
  marqueeContainer.appendChild(marqueeText.cloneNode(true));
  marqueeContainer.appendChild(marqueeText.cloneNode(true));
});
