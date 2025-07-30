import {
	useBlockProps,
	RichText,
	InspectorControls,
} from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";
import { PanelBody, ToggleControl } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const [email, setEmail] = useState("");
	const [loading, setLoading] = useState(true);

	// New attributes with enablePrefix and enableSuffix
	const enablePrefix = attributes?.enablePrefix ?? true;
	const enableSuffix = attributes?.enableSuffix ?? true;
	const prefix = attributes?.prefix || "";
	const suffix = attributes?.suffix || "";

	const showAsLink = attributes?.showAsLink ?? false;
	const underline = !!attributes?.underline;

	useEffect(() => {
		let isMounted = true;
		setLoading(true);

		// Fetch the latest store-info post's email via REST API
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
									const emailValue = Array.isArray(post?.meta?.email)
										? post.meta.email[0]
										: post?.meta?.email;
									setEmail(
										(typeof emailValue === "string" ? emailValue : "").trim(),
									);
									setLoading(false);
								}
							})
							.catch(() => {
								if (isMounted) {
									setEmail("");
									setLoading(false);
								}
							});
					} else {
						setEmail("");
						setLoading(false);
					}
				}
			})
			.catch(() => {
				if (isMounted) {
					setEmail("");
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
								className="store-info__email-prefix"
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
								className="store-info__email-suffix"
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
				<PanelBody title="Email Link" initialOpen={true}>
					<ToggleControl
						label="Show as link"
						checked={showAsLink}
						onChange={(value) => setAttributes({ showAsLink: value })}
					/>
					{showAsLink && (
						<ToggleControl
							label="Underline email address"
							checked={underline}
							onChange={(value) => setAttributes({ underline: value })}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<p
				{...useBlockProps({
					className: "store-info__email",
				})}
			>
				{enablePrefix && (
					<>
						<RichText
							tagName="span"
							className="store-info__email-prefix"
							value={prefix}
							onChange={(value) => setAttributes({ prefix: value })}
							placeholder="Prefix…"
							allowedFormats={[]}
						/>
						&nbsp;
					</>
				)}
				{loading ? (
					<span className="store-info__email-loading">
						Loading email address…
					</span>
				) : email ? (
					showAsLink ? (
						<a
							href={`mailto:${email}`}
							className="store-info__email-link"
							style={{
								textDecoration: underline ? "underline" : "none",
							}}
						>
							{email}
						</a>
					) : (
						<span className="store-info__email-text">{email}</span>
					)
				) : (
					<span className="store-info__email-empty">No email address</span>
				)}
				{enableSuffix && (
					<>
						&nbsp;
						<RichText
							tagName="span"
							className="store-info__email-suffix"
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
