// Login block

const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { registerBlockType } = wp.blocks;
const { CheckboxControl, TextControl, PanelBody } = wp.components;
const { serverSideRender: ServerSideRender } = wp;

export default registerBlockType('magic-login/login-block', {
	title: __('Magic Login', 'magic-login'),
	description: __('Passwordless login block', 'magic-login'),
	// https://raw.githubusercontent.com/ionic-team/ionicons/master/docs/ionicons/svg/md-color-wand.svg
	icon: (
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
			<path d="M200.8 157.2l-36.4 37.4L411.7 448l36.3-37.4zM181 64h37v68h-37zM181 262h37v68h-37zM270 176h69v37h-69zM305.6 115.8l-25.7-26.3-47.1 48.3 25.6 26.2zM168.8 137.8l-47.1-48.3-25.6 26.3 47.1 48.2zM96.1 277.9l25.6 26.2 47.1-48.2-25.6-26.3zM64 176h65v37H64z" />
		</svg>
	),
	keywords: [
		__('magic-login', 'magic-login'),
		__('login', 'magic-login'),
		__('magic', 'magic-login'),
		__('passwordless', 'magic-login'),
	],
	supports: {
		align: true,
		multiple: false,
	},
	attributes: {
		title: {
			type: 'string',
			default: __('Login with Email', 'magic-login'),
		},
		description: {
			type: 'string',
			default: __(
				'Please enter your username or email address. You will receive an email message to log in.',
				'magic-login',
			),
		},
		loginLabel: {
			type: 'string',
			default: __('Username or Email Address', 'magic-login'),
		},
		buttonLabel: {
			type: 'string',
			default: __('Send me the link', 'magic-login'),
		},
		redirectTo: {
			type: 'string',
		},
		hideLoggedIn: {
			type: 'boolean',
			default: true,
		},
		hideFormAfterSubmit: {
			type: 'boolean',
			default: true,
		},
		cancelRedirection: {
			type: 'boolean',
			default: false,
		},
	},
	edit: (props) => {
		const {
			attributes: {
				title,
				description,
				loginLabel,
				buttonLabel,
				redirectTo,
				hideLoggedIn,
				hideFormAfterSubmit,
				cancelRedirection,
			},
			className,
			setAttributes,
		} = props;

		return (
			<div className={className}>
				<ServerSideRender
					block="magic-login/login-block"
					attributes={{
						title,
						description,
						loginLabel,
						buttonLabel,
						redirectTo,
						hideLoggedIn,
						hideFormAfterSubmit,
					}}
				/>
				<Fragment>
					<InspectorControls>
						<PanelBody>
							<TextControl
								label={__('Title', 'magic-login')}
								value={title}
								onChange={(value) => setAttributes({ title: value })}
							/>
							<TextControl
								label={__('Description', 'magic-login')}
								value={description}
								onChange={(value) => setAttributes({ description: value })}
							/>
							<TextControl
								label={__('Login Label', 'magic-login')}
								value={loginLabel}
								onChange={(value) => setAttributes({ loginLabel: value })}
							/>
							<TextControl
								label={__('Button Label', 'magic-login')}
								value={buttonLabel}
								onChange={(value) => setAttributes({ buttonLabel: value })}
							/>
							{!cancelRedirection && (
								<TextControl
									label={__('Redirect URL', 'magic-login')}
									help={__('Leave it empty to use this page', 'magic-login')}
									value={redirectTo}
									onChange={(value) => setAttributes({ redirectTo: value })}
								/>
							)}

							<CheckboxControl
								label={__('Hide the block when user is logged-in', 'magic-login')}
								checked={hideLoggedIn}
								onChange={() => {
									setAttributes({ hideLoggedIn: !hideLoggedIn });
								}}
							/>
							<CheckboxControl
								label={__("Don't display once submitted", 'magic-login')}
								help={__(
									'Hide the login form after sending the request',
									'magic-login',
								)}
								checked={hideFormAfterSubmit}
								onChange={() => {
									setAttributes({ hideFormAfterSubmit: !hideFormAfterSubmit });
								}}
							/>
							<CheckboxControl
								label={__('Cancel redirection', 'magic-login')}
								help={__(
									'Cancel custom redirection for this login form',
									'magic-login',
								)}
								checked={cancelRedirection}
								onChange={() => {
									setAttributes({ cancelRedirection: !cancelRedirection });
								}}
							/>
						</PanelBody>
					</InspectorControls>
				</Fragment>
			</div>
		);
	},
	save: () => {
		return null;
	},
});
