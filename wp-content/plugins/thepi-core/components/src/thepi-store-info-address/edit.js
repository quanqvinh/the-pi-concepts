import {
	useBlockProps,
	RichText,
	InspectorControls,
} from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";
import {
	PanelBody,
	ToggleControl,
	TextControl,
	SelectControl,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const [address, setAddress] = useState("");
	const [loading, setLoading] = useState(true);

	// New attributes with enablePrefix and enableSuffix
	const enablePrefix = attributes?.enablePrefix ?? true;
	const enableSuffix = attributes?.enableSuffix ?? true;
	const prefix = attributes?.prefix || "";
	const suffix = attributes?.suffix || "";

	const showAsLink = attributes?.showAsLink ?? true;
	const linkTarget = attributes?.linkTarget || "_blank";
	const underline = !!attributes?.underline;
	const linkUrl = attributes?.link || "";

	useEffect(() => {
		let isMounted = true;
		setLoading(true);

		// Fetch the latest store-info post's address via REST API
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
									const addressValue = Array.isArray(post?.meta?.address)
										? post.meta.address[0]
										: post?.meta?.address;
									setAddress(
										(typeof addressValue === "string"
											? addressValue
											: ""
										).trim(),
									);
									setLoading(false);
								}
							})
							.catch(() => {
								if (isMounted) {
									setAddress("");
									setLoading(false);
								}
							});
					} else {
						setAddress("");
						setLoading(false);
					}
				}
			})
			.catch(() => {
				if (isMounted) {
					setAddress("");
					setLoading(false);
				}
			});

		return () => {
			isMounted = false;
		};
	}, []);

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
								className="store-info__address-prefix"
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
								className="store-info__address-suffix"
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
				<PanelBody title="Address Link" initialOpen={true}>
					<ToggleControl
						label="Show as link"
						checked={showAsLink}
						onChange={(value) => setAttributes({ showAsLink: value })}
					/>
					{showAsLink && (
						<>
							<TextControl
								label="Link URL"
								value={linkUrl}
								onChange={(value) => setAttributes({ link: value })}
								placeholder="https://example.com"
							/>
							<SelectControl
								label="Link Target"
								value={linkTarget}
								options={[
									{ label: "New tab (_blank)", value: "_blank" },
									{ label: "Same tab (_self)", value: "_self" },
								]}
								onChange={(value) => setAttributes({ linkTarget: value })}
							/>
							<ToggleControl
								label="Underline address"
								checked={underline}
								onChange={(value) => setAttributes({ underline: value })}
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>
			<p
				{...useBlockProps({
					className: "store-info__address",
				})}
			>
				{enablePrefix && (
					<>
						<RichText
							tagName="span"
							className="store-info__address-prefix"
							value={prefix}
							onChange={(value) => setAttributes({ prefix: value })}
							placeholder="Prefix…"
							allowedFormats={[]}
						/>
						&nbsp;
					</>
				)}
				{loading ? (
					<span className="store-info__address-loading">Loading address…</span>
				) : address ? (
					showAsLink && linkUrl ? (
						<a
							href={linkUrl}
							className="store-info__address-link"
							target={linkTarget}
							rel={linkTarget === "_blank" ? "noopener noreferrer" : undefined}
							style={{
								textDecoration: underline ? "underline" : "none",
							}}
						>
							{address}
						</a>
					) : (
						<span className="store-info__address-text">{address}</span>
					)
				) : (
					<span className="store-info__address-empty">No address</span>
				)}
				{enableSuffix && (
					<>
						&nbsp;
						<RichText
							tagName="span"
							className="store-info__address-suffix"
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
