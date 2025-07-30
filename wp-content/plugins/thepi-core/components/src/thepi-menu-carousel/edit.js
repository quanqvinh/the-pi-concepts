import {
	InspectorControls,
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
} from "@wordpress/block-editor";
import { PanelBody, Button, IconButton } from "@wordpress/components";
import { useEffect, useRef, useState } from "react";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: "thepi-menu-carousel",
	});

	// images is now an array of image IDs
	const imageIds = Array.isArray(attributes.images) ? attributes.images : [];

	// Store image data for preview
	const [imageData, setImageData] = useState([]);

	// Fetch image data for all IDs
	useEffect(() => {
		let isMounted = true;
		if (!imageIds.length) {
			setImageData([]);
			return;
		}
		// Fetch all images in one request if possible
		const fetchImages = async () => {
			const idsParam = imageIds.join(",");
			try {
				const res = await fetch(
					`/wp-json/wp/v2/media?include=${idsParam}&per_page=100`,
				);
				const data = await res.json();
				// Map by ID for fast lookup
				const dataById = {};
				data.forEach((img) => {
					dataById[img.id] = img;
				});
				// Order as per imageIds
				const ordered = imageIds.map((id) => dataById[id] || null);
				if (isMounted) setImageData(ordered);
			} catch (e) {
				if (isMounted) setImageData([]);
			}
		};
		fetchImages();
		return () => {
			isMounted = false;
		};
	}, [imageIds]);

	// Helper to update a single image at index
	const updateImage = (index, media) => {
		const newIds = [...imageIds];
		newIds[index] = media?.id || 0;
		setAttributes({ images: newIds });
	};

	// Helper to add a new image slot (default to 0, which is invalid)
	const addImage = () => {
		const newIds = [...imageIds, 0];
		setAttributes({ images: newIds });
	};

	// Helper to remove an image slot
	const removeImage = (index) => {
		const newIds = imageIds.filter((_, i) => i !== index);
		setAttributes({ images: newIds });
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
	}, [imageData, attributes.aspectRatio]);

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Menu Carousel Images" initialOpen={true}>
					{imageIds.length === 0 && (
						<div style={{ marginBottom: "1em" }}>No images selected.</div>
					)}
					{imageIds.map((id, index) => {
						const img = imageData[index];
						const imageUrl =
							img?.media_details?.sizes?.thumbnail?.source_url ||
							img?.source_url ||
							"";
						const imageAlt = img?.alt_text || "";
						const imageTitle = img?.title?.rendered || "";
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
										value={id}
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
								<div
									style={{
										flex: 1,
										marginRight: "0.5em",
										alignSelf: "stretch",
										display: "flex",
										flexDirection: "column",
										justifyContent: "center",
									}}
								>
									<div style={{ fontSize: 12, color: "#666", marginBottom: 4 }}>
										{imageTitle || "No title"}
									</div>
									<div style={{ fontSize: 11, color: "#aaa" }}>
										{imageAlt ? `Alt: ${imageAlt}` : ""}
									</div>
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
					{/* Keep aspect ratio control */}
					<input
						type="text"
						className="components-text-control__input"
						style={{ width: "100%", marginTop: 8 }}
						placeholder="12/13"
						value={attributes.aspectRatio || ""}
						onChange={(e) => setAttributes({ aspectRatio: e.target.value })}
						aria-label="Aspect Ratio"
					/>
					<div style={{ fontSize: 12, color: "#666" }}>
						Format: width/height (e.g. 12/13)
					</div>
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
					{imageData &&
						imageData.length > 0 &&
						imageData.map((img, idx) =>
							img && img.source_url ? (
								<img
									key={idx}
									src={img.source_url}
									alt={img.alt_text || ""}
									title={img.title?.rendered || ""}
									className={`thepi-menu-carousel__slide ${
										idx === 0 ? "active" : ""
									}`}
									data-carousel-index={idx}
								/>
							) : null,
						)}
				</div>
				{imageData?.length > 0 && (
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
