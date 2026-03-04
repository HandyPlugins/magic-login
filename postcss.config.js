/**
 * Exports the PostCSS configuration.
 *
 * @return {string} PostCSS options.
 */
const postcssImport = require( 'postcss-import' );
const postcssPresetEnv = require( 'postcss-preset-env' );
const cssnano = require( 'cssnano' );

const removeSharedUiBunnyFontImport = () => ( {
	postcssPlugin: 'remove-shared-ui-bunny-font-import',
	AtRule: {
		import: ( atRule ) => {
			if ( atRule.params && atRule.params.includes( 'fonts.bunny.net/css?family=Roboto' ) ) {
				atRule.remove();
			}
		},
	},
} );
removeSharedUiBunnyFontImport.postcss = true;

module.exports = ( { file, options, env } ) => ( { /* eslint-disable-line */
	plugins: [
		postcssImport(),
		removeSharedUiBunnyFontImport(),
		postcssPresetEnv( {
			stage: 0,
			autoprefixer: {
				grid: true,
			},
		} ),
		// Minify style on production using cssano.
		...( env === 'production'
			? [
					cssnano( {
						preset: [
							'default',
							{
								autoprefixer: false,
								calc: {
									precision: 8,
								},
								convertValues: true,
								discardComments: {
									removeAll: true,
								},
								mergeLonghand: false,
								zindex: false,
							},
						],
					} ),
			  ]
			: [] ),
	],
} );
