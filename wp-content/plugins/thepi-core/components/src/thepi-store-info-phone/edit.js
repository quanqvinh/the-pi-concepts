import {
	useBlockProps,
	RichText,
	InspectorControls,
} from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";
import { PanelBody, SelectControl, ToggleControl } from "@wordpress/components";

// Helper to create tel: href like render.php (replace leading ^0 with +84)
const getTelHref = (rawPhone) => {
	if (!rawPhone) return "";
	return rawPhone.replace(/^0/, "+84");
};

export default function Edit({ attributes, setAttributes }) {
	const [phone, setPhone] = useState("");
	const [loading, setLoading] = useState(true);

	// New attributes with enablePrefix and enableSuffix
	const enablePrefix = attributes?.enablePrefix ?? true;
	const enableSuffix = attributes?.enableSuffix ?? true;
	const prefix = attributes?.prefix || "";
	const suffix = attributes?.suffix || "";

	const separator =
		typeof attributes?.separator === "string" ? attributes.separator : " ";

	const showAsLink = attributes?.showAsLink ?? false;
	const underline = !!attributes?.underline;

	// Helper to format phone number with separator
	const formatPhoneWithSeparator = (rawPhone, sep) => {
		if (!rawPhone) return "";
		// Replace all whitespace, dot, or dash with the separator
		return rawPhone.replace(/[\s.-]+/g, sep);
	};

	useEffect(() => {
		let isMounted = true;
		setLoading(true);

		// Fetch the latest store-info post's phone number via REST API
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
									const phoneValue = Array.isArray(post?.meta?.phone)
										? post.meta.phone[0]
										: post?.meta?.phone;
									setPhone(
										(typeof phoneValue === "string" ? phoneValue : "").trim(),
									);
									setLoading(false);
								}
							})
							.catch(() => {
								if (isMounted) {
									setPhone("");
									setLoading(false);
								}
							});
					} else {
						setPhone("");
						setLoading(false);
					}
				}
			})
			.catch(() => {
				if (isMounted) {
					setPhone("");
					setLoading(false);
				}
			});

		return () => {
			isMounted = false;
		};
	}, []);

	const formattedPhone = formatPhoneWithSeparator(phone, separator);
	const telHref = phone ? `tel:${getTelHref(phone)}` : "";

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
								className="store-info__phone-prefix"
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
								className="store-info__phone-suffix"
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
				<PanelBody title="Phone Number Separator" initialOpen={true}>
					<SelectControl
						label="Separator"
						value={attributes?.separator ?? " "}
						options={[
							{ label: "Space", value: " " },
							{ label: "Dash (-)", value: "-" },
							{ label: "Dot (.)", value: "." },
							{ label: "None", value: "" },
						]}
						onChange={(value) => setAttributes({ separator: value })}
					/>
				</PanelBody>
				<PanelBody title="Phone Link" initialOpen={true}>
					<ToggleControl
						label="Show as link"
						checked={showAsLink}
						onChange={(value) => setAttributes({ showAsLink: value })}
					/>
					{showAsLink && (
						<ToggleControl
							label="Underline phone number"
							checked={underline}
							onChange={(value) => setAttributes({ underline: value })}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<p
				{...useBlockProps({
					className: "store-info__phone",
				})}
			>
				{enablePrefix && (
					<>
						<RichText
							tagName="span"
							className="store-info__phone-prefix"
							value={prefix}
							onChange={(value) => setAttributes({ prefix: value })}
							placeholder="Prefix…"
							allowedFormats={[]}
						/>
						&nbsp;
					</>
				)}
				{loading ? (
					<span className="store-info__phone-loading">
						Loading phone number…
					</span>
				) : formattedPhone ? (
					showAsLink ? (
						<a
							href={telHref}
							className="store-info__phone-link"
							style={{
								textDecoration: underline ? "underline" : "none",
							}}
						>
							{formattedPhone}
						</a>
					) : (
						<span className="store-info__phone-text">{formattedPhone}</span>
					)
				) : (
					<span className="store-info__phone-empty">No phone number</span>
				)}
				{enableSuffix && (
					<>
						&nbsp;
						<RichText
							tagName="span"
							className="store-info__phone-suffix"
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
