import {
	InspectorControls,
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
} from "@wordpress/block-editor";
import {
	PanelBody,
	Button,
	IconButton,
	TextControl,
} from "@wordpress/components";
import { useEffect, useRef } from "react";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: "thepi-menu-carousel",
	});

	// Use block attribute for images, fallback to empty array
	const images = attributes.images || [];

	// Helper to update a single image at index
	const updateImage = (index, media) => {
		const newImages = [...images];
		newImages[index] = {
			url: media?.url || "",
			name: media?.title || media?.filename || "",
			alt: media?.alt || "",
		};
		setAttributes({ images: newImages });
	};

	// Helper to update image alt or name at index
	const updateImageField = (index, field, value) => {
		const newImages = [...images];
		newImages[index] = {
			...(newImages[index] || {}),
			[field]: value,
		};
		setAttributes({ images: newImages });
	};

	// Helper to add a new image slot
	const addImage = () => {
		const newImages = [...images, { url: "", name: "", alt: "" }];
		setAttributes({ images: newImages });
	};

	// Helper to remove an image slot
	const removeImage = (index) => {
		const newImages = images.filter((_, i) => i !== index);
		setAttributes({ images: newImages });
	};

	const carouselRef = useRef(null);

	// Parse aspect ratio string (e.g. "12/13") to a CSS calc() value
	const getAspectRatioPadding = () => {
		const aspectRatio = attributes.aspectRatio || "12/13";
		const [w, h] = aspectRatio.split("/").map(Number);
		if (!w || !h) return "calc(100% * 13 / 12)";
		return `calc(100% * ${h} / ${w})`;
	};

	function addMenuCarouselBehaviour() {
		if (!carouselRef.current) return;
		var carousel = carouselRef.current;
		var slides = carousel.querySelectorAll(".thepi-menu-carousel__slide");
		var current = 0;

		function showSlide(idx) {
			slides.forEach(function (slide, i) {
				if (i === idx) {
					slide.classList.add("active");
				} else {
					slide.classList.remove("active");
				}
			});
		}
		showSlide(current);

		if (slides.length) {
			var prevBtn = carousel.querySelector(".thepi-menu-carousel__prev");
			var nextBtn = carousel.querySelector(".thepi-menu-carousel__next");
			if (prevBtn && nextBtn) {
				prevBtn.addEventListener("click", prevHandler);
				nextBtn.addEventListener("click", nextHandler);
			}
		}

		function prevHandler() {
			current = (current - 1 + slides.length) % slides.length;
			showSlide(current);
		}
		function nextHandler() {
			current = (current + 1) % slides.length;
			showSlide(current);
		}

		// Cleanup function to remove event listeners
		return () => {
			if (slides.length) {
				var prevBtn = carousel.querySelector(".thepi-menu-carousel__prev");
				var nextBtn = carousel.querySelector(".thepi-menu-carousel__next");
				if (prevBtn && nextBtn) {
					prevBtn.removeEventListener("click", prevHandler);
					nextBtn.removeEventListener("click", nextHandler);
				}
			}
		};
	}

	useEffect(() => {
		const cleanup = addMenuCarouselBehaviour();
		return () => {
			if (typeof cleanup === "function") cleanup();
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [images, attributes.aspectRatio]);

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Menu Carousel Images" initialOpen={true}>
					{images.length === 0 && (
						<div style={{ marginBottom: "1em" }}>No images selected.</div>
					)}
					{images.map((imageObj, index) => {
						const imageUrl = imageObj?.url || "";
						const imageName = imageObj?.name || "";
						const imageAlt = imageObj?.alt || "";
						return (
							<div
								key={index}
								className="menu-image-input-item"
								style={{
									display: "flex",
									alignItems: "flex-start",
									marginBottom: "0.75em",
									gap: "4px",
								}}
							>
								<MediaUploadCheck>
									<MediaUpload
										onSelect={(media) => updateImage(index, media)}
										allowedTypes={["image"]}
										value={imageUrl}
										render={({ open }) => (
											<Button
												onClick={open}
												style={{
													padding: 0,
													border: "none",
													background: "none",
													marginRight: "0.5em",
													height: "auto",
												}}
												className="menu-image-button"
												aria-label="Select image"
											>
												{imageUrl ? (
													<img
														src={imageUrl}
														alt={imageAlt}
														style={{
															width: 48,
															height: 48,
															objectFit: "cover",
															border: "1px solid #ccc",
															borderRadius: 4,
														}}
													/>
												) : (
													<div
														style={{
															width: 48,
															height: 48,
															display: "flex",
															alignItems: "center",
															justifyContent: "center",
															background: "#f0f0f0",
															border: "1px solid #ccc",
															borderRadius: 4,
															color: "#888",
															fontSize: 12,
														}}
													>
														Select
													</div>
												)}
											</Button>
										)}
									/>
								</MediaUploadCheck>
								<div style={{ flex: 1, marginRight: "0.5em" }}>
									<TextControl
										label="Image Name"
										value={imageName}
										onChange={(val) => updateImageField(index, "name", val)}
										placeholder="Image name"
										style={{ marginBottom: 4 }}
									/>
									<TextControl
										label="Alt Text"
										value={imageAlt}
										onChange={(val) => updateImageField(index, "alt", val)}
										placeholder="Image alt text"
									/>
								</div>
								<IconButton
									icon="no-alt"
									label="Remove image"
									onClick={() => removeImage(index)}
								/>
							</div>
						);
					})}
					<Button isSecondary onClick={addImage} icon="plus">
						Add Image
					</Button>
				</PanelBody>
				<PanelBody title="Carousel Settings" initialOpen={true}>
					<TextControl
						label="Aspect Ratio"
						help="Format: width/height (e.g. 12/13)"
						value={attributes.aspectRatio || ""}
						onChange={(val) => setAttributes({ aspectRatio: val })}
						placeholder="12/13"
					/>
				</PanelBody>
			</InspectorControls>
			<div
				ref={carouselRef}
				className="thepi-menu-carousel__container"
				style={{
					paddingTop: getAspectRatioPadding(),
					transition: "padding-top 0.2s",
				}}
			>
				<div className="thepi-menu-carousel__slides">
					{images &&
						images.length > 0 &&
						images.map((imageObj, idx) =>
							imageObj?.url ? (
								<img
									key={idx}
									src={imageObj.url}
									alt={imageObj.alt || ""}
									title={imageObj.name || ""}
									className={`thepi-menu-carousel__slide ${
										idx === 0 ? "active" : ""
									}`}
									data-carousel-index={idx}
								/>
							) : null,
						)}
				</div>
				{images?.length > 0 && (
					<>
						<button
							type="button"
							className="thepi-menu-carousel__prev"
						></button>
						<button
							type="button"
							className="thepi-menu-carousel__next"
						></button>
					</>
				)}
			</div>
		</div>
	);
}
