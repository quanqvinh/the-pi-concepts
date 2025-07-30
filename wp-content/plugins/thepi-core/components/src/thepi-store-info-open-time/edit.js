import {
	useBlockProps,
	RichText,
	InspectorControls,
} from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";
import { PanelBody, ToggleControl, SelectControl } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const [openTime, setOpenTime] = useState("");
	const [closeTime, setCloseTime] = useState("");
	const [loading, setLoading] = useState(true);

	const enablePrefix = attributes?.enablePrefix ?? true;
	const enableSuffix = attributes?.enableSuffix ?? true;
	const prefix = attributes?.prefix || "";
	const suffix = attributes?.suffix || "";
	const displayFormat = attributes?.displayFormat || "24h";
	const separator = attributes?.separator || " - ";
	const amPmCase = attributes?.amPmCase || "upper";
	const amPmSpacing = attributes?.amPmSpacing ?? false;

	useEffect(() => {
		let isMounted = true;
		setLoading(true);

		// Fetch the latest store-info post's open/close time via REST API
		fetch(
			"/wp-json/wp/v2/store-info?per_page=1&orderby=date&order=desc&_fields=id",
		)
			.then((res) => res.json())
			.then((posts) => {
				if (isMounted) {
					if (posts && posts.length > 0) {
						const postId = posts[0].id;
						fetch(`/wp-json/wp/v2/store-info/${postId}?_fields=meta`)
							.then((res) => res.json())
							.then((post) => {
								if (isMounted) {
									const openValue = Array.isArray(post?.meta?.open_time)
										? post.meta.open_time[0]
										: post?.meta?.open_time;
									const closeValue = Array.isArray(post?.meta?.close_time)
										? post.meta.close_time[0]
										: post?.meta?.close_time;
									setOpenTime(
										(typeof openValue === "string" ? openValue : "").trim(),
									);
									setCloseTime(
										(typeof closeValue === "string" ? closeValue : "").trim(),
									);
									setLoading(false);
								}
							})
							.catch(() => {
								if (isMounted) {
									setOpenTime("");
									setCloseTime("");
									setLoading(false);
								}
							});
					} else {
						setOpenTime("");
						setCloseTime("");
						setLoading(false);
					}
				}
			})
			.catch(() => {
				if (isMounted) {
					setOpenTime("");
					setCloseTime("");
					setLoading(false);
				}
			});

		return () => {
			isMounted = false;
		};
	}, []);

	// Format time according to displayFormat, amPmCase, and amPmSpacing
	function formatTime(timeStr) {
		if (!timeStr) return "";
		if (displayFormat === "24h") {
			return timeStr;
		}
		// 12h format
		const [h, m] = timeStr.split(":");
		let hour = parseInt(h, 10);
		const min = m || "00";
		let ampm = hour >= 12 ? "PM" : "AM";
		if (amPmCase === "lower") {
			ampm = ampm.toLowerCase();
		} else {
			ampm = ampm.toUpperCase();
		}
		hour = hour % 12;
		if (hour === 0) hour = 12;
		const timeStr12h = `${hour.toString().padStart(2, "0")}:${min
			.toString()
			.padStart(2, "0")}`;
		return amPmSpacing ? `${timeStr12h} ${ampm}` : `${timeStr12h}${ampm}`;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title="Prefix/Suffix" initialOpen={true}>
					<ToggleControl
						label="Enable prefix"
						checked={enablePrefix}
						onChange={(value) => setAttributes({ enablePrefix: value })}
					/>
					{enablePrefix && (
						<div style={{ margin: "8px 0" }}>
							<RichText
								tagName="span"
								className="store-info__open-time-prefix"
								value={prefix}
								onChange={(value) => setAttributes({ prefix: value })}
								placeholder="Prefix…"
								allowedFormats={[]}
								style={{
									border: "1px solid #ddd",
									borderRadius: "4px",
									padding: "4px 8px",
									minWidth: "80px",
									display: "inline-block",
									background: "#fafbfc",
								}}
							/>
						</div>
					)}
					<ToggleControl
						label="Enable suffix"
						checked={enableSuffix}
						onChange={(value) => setAttributes({ enableSuffix: value })}
					/>
					{enableSuffix && (
						<div style={{ margin: "8px 0" }}>
							<RichText
								tagName="span"
								className="store-info__open-time-suffix"
								value={suffix}
								onChange={(value) => setAttributes({ suffix: value })}
								placeholder="Suffix…"
								allowedFormats={[]}
								style={{
									border: "1px solid #ddd",
									borderRadius: "4px",
									padding: "4px 8px",
									minWidth: "80px",
									display: "inline-block",
									background: "#fafbfc",
								}}
							/>
						</div>
					)}
				</PanelBody>
				<PanelBody title="Display Options" initialOpen={true}>
					<SelectControl
						label="Time format"
						value={displayFormat}
						options={[
							{ label: "24-hour (e.g. 09:00)", value: "24h" },
							{ label: "12-hour (e.g. 9:00AM)", value: "12h" },
						]}
						onChange={(value) => setAttributes({ displayFormat: value })}
					/>
					{displayFormat === "12h" && (
						<>
							<SelectControl
								label="AM/PM case"
								value={amPmCase}
								options={[
									{ label: "UPPERCASE (AM/PM)", value: "upper" },
									{ label: "lowercase (am/pm)", value: "lower" },
								]}
								onChange={(value) => setAttributes({ amPmCase: value })}
							/>
							<ToggleControl
								label="Add space before AM/PM"
								checked={amPmSpacing}
								onChange={(value) => setAttributes({ amPmSpacing: value })}
							/>
						</>
					)}
					<div style={{ marginTop: "12px" }}>
						<label htmlFor="store-info-open-time-separator">
							Separator between open and close time
						</label>
						<input
							id="store-info-open-time-separator"
							type="text"
							value={separator}
							onChange={(e) => setAttributes({ separator: e.target.value })}
							style={{
								width: "80px",
								marginLeft: "8px",
								border: "1px solid #ddd",
								borderRadius: "4px",
								padding: "2px 6px",
							}}
						/>
					</div>
				</PanelBody>
			</InspectorControls>
			<p {...useBlockProps({ className: "store-info__open-time" })}>
				{enablePrefix && (
					<>
						<RichText
							tagName="span"
							className="store-info__open-time-prefix"
							value={prefix}
							onChange={(value) => setAttributes({ prefix: value })}
							placeholder="Prefix…"
							allowedFormats={[]}
						/>
						&nbsp;
					</>
				)}
				{loading ? (
					<span className="store-info__open-time-loading">Loading…</span>
				) : openTime || closeTime ? (
					<span className="store-info__open-time-value">
						{formatTime(openTime)}
						{separator}
						{formatTime(closeTime)}
					</span>
				) : (
					<span className="store-info__open-time-empty">
						No open/close time
					</span>
				)}
				{enableSuffix && (
					<>
						&nbsp;
						<RichText
							tagName="span"
							className="store-info__open-time-suffix"
							value={suffix}
							onChange={(value) => setAttributes({ suffix: value })}
							placeholder="Suffix…"
							allowedFormats={[]}
						/>
					</>
				)}
			</p>
		</>
	);
}
