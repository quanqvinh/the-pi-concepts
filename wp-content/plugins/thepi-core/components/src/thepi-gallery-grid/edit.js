import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	__experimentalNumberControl as NumberControl,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const {
		columns = 9,
		columnGap = 17,
		rowGap = 17,
		rowHeight = 400,
		showMore = false,
		showMoreAmountEachTime = 3,
		initialAmount = 7,
		enableLightBox = true,
	} = attributes;

	const blockProps = useBlockProps({
		className: "gallery-grid-wrapper",
	});

	const [items, setItems] = useState([]);
	const [totalCount, setTotalCount] = useState(0);
	const [loadedCount, setLoadedCount] = useState(0);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [showMoreLoading, setShowMoreLoading] = useState(false);

	// Fetch initial gallery items and total count
	useEffect(() => {
		let isMounted = true;
		setLoading(true);
		setError(null);

		// Fetch total count
		fetch("/wp-json/wp/v2/gallery?per_page=1&_fields=id")
			.then(async (res) => {
				let total = 0;
				const data = await res.json();
				const totalHeader = res.headers.get("X-WP-Total");
				if (totalHeader) {
					total = parseInt(totalHeader, 10);
				}
				// Fallback: try to get from data.length if only 1 event
				if (!total && Array.isArray(data)) {
					total = data.length;
				}
				if (isMounted) setTotalCount(total);
			})
			.catch(() => {
				if (isMounted) setTotalCount(0);
			});

		// Fetch initial items (ordered by is_highlighted DESC, date DESC)
		fetch(`/wp-json/thepi/v1/gallery/show-more?limit=${initialAmount}&offset=0`)
			.then((res) => res.json())
			.then((data) => {
				if (isMounted) {
					setItems(data || []);
					setLoadedCount(data ? data.length : 0);
					setLoading(false);
				}
			})
			.catch(() => {
				if (isMounted) {
					setItems([]);
					setLoadedCount(0);
					setLoading(false);
				}
			});

		return () => {
			isMounted = false;
		};
		// Only re-run if initialAmount changes
	}, [initialAmount]);

	const handleShowMore = () => {
		setShowMoreLoading(true);
		const nextOffset = loadedCount;
		const nextLimit = showMoreAmountEachTime;
		wp.apiFetch({
			path: `/thepi/v1/gallery/show-more?limit=${nextLimit}&offset=${nextOffset}`,
		})
			.then((data) => {
				setItems((prev) => [...prev, ...(data || [])]);
				setLoadedCount((prev) => prev + (data ? data.length : 0));
				setShowMoreLoading(false);
			})
			.catch(() => {
				setShowMoreLoading(false);
			});
	};

	const shouldShowMoreButton =
		showMore &&
		!loading &&
		!error &&
		items.length < totalCount &&
		totalCount > 0;

	if (loading) {
		return <div {...blockProps}>Loading gallery...</div>;
	}

	if (error) {
		return <div {...blockProps}>{error}</div>;
	}

	if (!items || items.length === 0) {
		return <div {...blockProps}>No image in gallery</div>;
	}

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Grid Settings" initialOpen={true}>
					<RangeControl
						label="Columns"
						value={columns}
						onChange={(val) => setAttributes({ columns: val })}
						min={1}
						max={20}
					/>
					<RangeControl
						label="Column Gap (px)"
						value={columnGap}
						onChange={(val) => setAttributes({ columnGap: val })}
						min={0}
						max={100}
					/>
					<RangeControl
						label="Row Gap (px)"
						value={rowGap}
						onChange={(val) => setAttributes({ rowGap: val })}
						min={0}
						max={100}
					/>
					<RangeControl
						label="Row Height (px)"
						value={rowHeight}
						onChange={(val) => setAttributes({ rowHeight: val })}
						min={50}
						max={1000}
					/>
				</PanelBody>
				<PanelBody title="Event Display" initialOpen={true}>
					<RangeControl
						label="Initial Amount"
						value={initialAmount}
						onChange={(val) => setAttributes({ initialAmount: val })}
						min={1}
						max={20}
					/>
					<ToggleControl
						label="Enable Show More Button"
						checked={!!showMore}
						onChange={(value) => setAttributes({ showMore: value })}
					/>
					{showMore && (
						<NumberControl
							label="Show More: Items Each Time"
							value={showMoreAmountEachTime}
							onChange={(value) =>
								setAttributes({
									showMoreAmountEachTime: value ? parseInt(value, 10) : 1,
								})
							}
							min={1}
							max={20}
							isShiftStepEnabled={true}
							shiftStep={5}
						/>
					)}
				</PanelBody>
				<PanelBody title="Lightbox Settings" initialOpen={true}>
					<ToggleControl
						label="Enable Lightbox"
						checked={!!enableLightBox}
						onChange={(value) => setAttributes({ enableLightBox: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<div
				id="gallery-grid"
				style={{
					display: "grid",
					gridTemplateColumns: `repeat(${parseInt(columns) || 9}, 1fr)`,
					columnGap: `${columnGap}px`,
					rowGap: `${rowGap}px`,
					gridAutoRows: `${rowHeight}px`,
				}}
			>
				{items.map((item, idx) => (
					<div
						key={item.id || idx}
						className="gallery-item"
						style={{
							height: `${rowHeight}px`,
						}}
					>
						{item.thumbnail ? (
							<img
								src={item.thumbnail}
								alt={item.title}
								style={{
									width: "100%",
									height: "100%",
									objectFit: "cover",
									objectPosition: "center",
								}}
							/>
						) : (
							<div
								style={{
									width: "100%",
									height: "150px",
									background: "#eee",
									display: "flex",
									alignItems: "center",
									justifyContent: "center",
									color: "#aaa",
								}}
							>
								No image
							</div>
						)}
					</div>
				))}
			</div>
			{shouldShowMoreButton && (
				<button
					className="gallery-show-more wp-element-button"
					disabled={showMoreLoading}
					onClick={handleShowMore}
					style={{ marginTop: "110px" }}
				>
					{showMoreLoading ? "Loading..." : "Show More"}
				</button>
			)}
		</div>
	);
}
