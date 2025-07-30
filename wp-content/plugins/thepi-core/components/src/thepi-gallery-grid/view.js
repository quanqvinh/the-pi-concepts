import PhotoSwipeLightbox from "photoswipe/lightbox";
import "photoswipe/style.css";

document.addEventListener("DOMContentLoaded", () => {
	const galleryGrid = document.querySelector(
		"#gallery-grid[data-lightbox-enable='true']",
	);

	/**
	 * Sets the data-pswp-width and data-pswp-height attributes for each gallery item
	 * based on the natural size of the image and the current window size.
	 * @param {HTMLElement} grid
	 */
	function updateGalleryItemImages(grid) {
		if (!grid) return;
		const galleryItems = grid.querySelectorAll(".gallery-item");

		galleryItems.forEach((galleryItem) => {
			const imgEl = galleryItem.querySelector("img");
			if (!imgEl) return;

			const setImageSize = () => {
				const { naturalWidth, naturalHeight } = imgEl;
				if (!naturalWidth || !naturalHeight) return;

				const imgRatio = naturalWidth / naturalHeight;
				const windowRatio = window.innerWidth / window.innerHeight;

				let newWidth, newHeight;

				if (imgRatio > windowRatio) {
					newWidth = window.innerWidth;
					newHeight = Math.round(newWidth / imgRatio);
				} else {
					newHeight = window.innerHeight;
					newWidth = Math.round(newHeight * imgRatio);
				}

				galleryItem.setAttribute("data-pswp-width", newWidth);
				galleryItem.setAttribute("data-pswp-height", newHeight);
			};

			if (imgEl.complete && imgEl.naturalWidth && imgEl.naturalHeight) {
				setImageSize();
			} else {
				imgEl.addEventListener("load", setImageSize, { once: true });
			}
		});
	}

	// Initial run
	updateGalleryItemImages(galleryGrid);

	// Recalculate on window resize for responsiveness
	let resizeTimeout;
	window.addEventListener("resize", () => {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(() => updateGalleryItemImages(galleryGrid), 150);
	});

	// Observe changes to galleryGrid and update images accordingly
	if (galleryGrid) {
		const observer = new MutationObserver((mutationsList) => {
			for (const mutation of mutationsList) {
				if (
					mutation.type === "childList" &&
					(mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0)
				) {
					updateGalleryItemImages(galleryGrid);
					break;
				}
			}
		});
		observer.observe(galleryGrid, { childList: true, subtree: false });
	}

	const lightbox = new PhotoSwipeLightbox({
		gallery: "#gallery-grid[data-lightbox-enable='true']",
		childSelector: ".gallery-item",
		pswpModule: () => import("photoswipe"),
		showHideAnimationType: "zoom",
		padding: { top: 70, bottom: 40, left: 100, right: 100 },
	});

	if (
		galleryGrid &&
		galleryGrid.getAttribute("data-show-more-displayed") === "true"
	) {
		lightbox.on("uiRegister", function () {
			lightbox.pswp.ui.registerElement({
				name: "load-more",
				order: 5,
				isButton: true,
				html: "Load more",
				onClick: async (event, el) => {
					const showMoreBtn = document.querySelector(".gallery-show-more");
					if (
						!showMoreBtn ||
						showMoreBtn.disabled ||
						!showMoreBtn.offsetParent
					) {
						// No more items to load or button not visible
						galleryGrid.classList.add("pswp__hide-load-more");
						return;
					}

					el.innerText = "Loading...";
					el.disabled = true;

					const onLoadMoreDone = () => {
						const stillVisible =
							showMoreBtn &&
							showMoreBtn.offsetParent !== null &&
							!showMoreBtn.disabled;
						if (stillVisible) {
							el.innerText = "Load more";
							el.disabled = false;
						} else {
							galleryGrid.classList.add("pswp__hide-load-more");
						}

						if (lightbox.pswp) {
							const items = Array.from(
								galleryGrid.querySelectorAll(".gallery-item"),
							);
							lightbox.pswp.options.dataSource = items;
							for (let i = 0; i < items.length; ++i) {
								lightbox.pswp.refreshSlideContent(i);
							}
						}
					};

					galleryGrid.addEventListener("grid-load-more-done", onLoadMoreDone, {
						once: true,
					});

					// Trigger the "Show More" button click
					showMoreBtn.click();
				},
			});
		});
	}

	lightbox.init();
});
