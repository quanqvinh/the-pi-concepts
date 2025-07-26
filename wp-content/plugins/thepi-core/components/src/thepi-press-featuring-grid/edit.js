/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	ToggleControl,
	RangeControl,
	Button,
} from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

export default function Edit({ attributes, setAttributes }) {
	const {
		gapX = 19,
		gapY = 50,
		initialAmount = 3,
		showMore = false,
		showMoreAmountEachTime = 3,
	} = attributes;

	const gridStyle = {
		gap: `${gapY ?? 0}px ${gapX ?? 0}px`,
	};

	const [pressFeaturing, setPressFeaturing] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [totalCount, setTotalCount] = useState(0);
	const [loadedCount, setLoadedCount] = useState(initialAmount);
	const [showMoreLoading, setShowMoreLoading] = useState(false);

	// Fetch total count and initial items
	useEffect(() => {
		let isMounted = true;
		setLoading(true);
		setError(null);

		// Fetch total count
		fetch(`/wp-json/wp/v2/press-featuring?per_page=1`)
			.then(async (res) => {
				let total = 0;
				const data = await res.json();
				const totalHeader = res.headers.get("X-WP-Total");
				if (totalHeader) {
					total = parseInt(totalHeader, 10);
				}
				if (!total && Array.isArray(data)) {
					total = data.length;
				}
				if (isMounted) setTotalCount(total);
			})
			.catch(() => {
				if (isMounted) setTotalCount(0);
			});

		// Fetch initial items
		fetch(`/wp-json/wp/v2/press-featuring?per_page=${initialAmount}&_embed`)
			.then(async (res) => {
				const data = await res.json();
				if (isMounted) {
					setPressFeaturing(data);
					setLoadedCount(data.length);
					setLoading(false);
				}
			})
			.catch((err) => {
				if (isMounted) {
					setError("Failed to load press featuring");
					setLoading(false);
				}
			});

		return () => {
			isMounted = false;
		};
	}, [initialAmount]);

	const handleShowMore = () => {
		setShowMoreLoading(true);
		const nextOffset = loadedCount;
		const nextLimit = showMoreAmountEachTime;

		wp.apiFetch({
			path: `/wp/v2/press-featuring?per_page=${nextLimit}&offset=${nextOffset}&_embed`,
		})
			.then((data) => {
				setPressFeaturing((prev) => [...prev, ...data]);
				setLoadedCount((prev) => prev + data.length);
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
		pressFeaturing.length > 0 &&
		pressFeaturing.length < totalCount;

	if (!pressFeaturing || pressFeaturing.length === 0) {
		return <div {...useBlockProps()}>No press featuring found.</div>;
	}

	return (
		<div
			{...useBlockProps({
				className: "thepi-press-featuring-container",
				"data-initial-count": initialAmount,
				"data-show-more-each-time": showMoreAmountEachTime,
				style: {},
			})}
		>
			<InspectorControls>
				<PanelBody
					title={__("Grid Settings", "thepi-components")}
					initialOpen={true}
				>
					<RangeControl
						label={__("Horizontal Gap (gapX)", "thepi-components")}
						value={gapX}
						onChange={(value) => setAttributes({ gapX: value })}
						min={0}
						max={100}
						help={__(
							"Horizontal gap between cards (in px).",
							"thepi-components",
						)}
					/>
					<RangeControl
						label={__("Vertical Gap (gapY)", "thepi-components")}
						value={gapY}
						onChange={(value) => setAttributes({ gapY: value })}
						min={0}
						max={100}
						help={__("Vertical gap between cards (in px).", "thepi-components")}
					/>
				</PanelBody>
				<PanelBody
					title={__("Display Settings", "thepi-components")}
					initialOpen={true}
				>
					<RangeControl
						label={__("Initial Items to Show", "thepi-components")}
						value={initialAmount}
						onChange={(value) => setAttributes({ initialAmount: value })}
						min={1}
						max={20}
					/>
					<ToggleControl
						label={__("Enable Show More Button", "thepi-components")}
						checked={!!showMore}
						onChange={(value) => setAttributes({ showMore: value })}
					/>
					{showMore && (
						<RangeControl
							label={__("Show More: Items Each Time", "thepi-components")}
							value={showMoreAmountEachTime}
							onChange={(value) =>
								setAttributes({ showMoreAmountEachTime: value })
							}
							min={1}
							max={20}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<div className="thepi-press-featuring-grid" style={gridStyle}>
				{loading ? (
					<div>
						{__("Loading latest press featuring...", "thepi-components")}
					</div>
				) : error ? (
					<div>{error}</div>
				) : !pressFeaturing || pressFeaturing.length === 0 ? (
					<div>{__("No press featuring found.", "thepi-components")}</div>
				) : (
					pressFeaturing.map((press) => (
						<div
							key={press.id}
							className="thepi-press-featuring-item"
							data-press-id={press.id}
						>
							{press._embedded?.["wp:featuredmedia"]?.[0]?.source_url && (
								<div className="thepi-press-featuring-thumb">
									<img
										src={press._embedded["wp:featuredmedia"][0].source_url}
										alt={press.title.rendered}
									/>
								</div>
							)}
							<h3
								className="thepi-press-featuring-title"
								dangerouslySetInnerHTML={{ __html: press.title.rendered }}
							/>
							<p className="thepi-press-featuring-subtitle">
								{press.meta?.press_featuring_subtitle || ""}
							</p>
						</div>
					))
				)}
			</div>
			{shouldShowMoreButton && (
				<Button
					className="thepi-press-featuring-show-more"
					variant="primary"
					disabled={showMoreLoading}
					onClick={handleShowMore}
					style={{ marginTop: "110px" }}
				>
					{showMoreLoading
						? __("Loading...", "thepi-components")
						: __("Show More", "thepi-components")}
				</Button>
			)}
		</div>
	);
}
