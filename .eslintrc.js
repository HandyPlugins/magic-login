module.exports = {
	root: true,
	extends: ['plugin:@wordpress/eslint-plugin/recommended'],
	plugins: ['import'],
	globals: {
		wp: 'readonly',
		ajaxurl: 'readonly',
		FormData: 'readonly',
		jQuery: 'readonly',
	},
	rules: {
		'no-console': 'off',
		'@wordpress/i18n-text-domain': [
			'error',
			{
				allowedTextDomain: 'magic-login',
			},
		],
	},
};
