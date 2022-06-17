// Run when DOM is loaded.
document.addEventListener('DOMContentLoaded', function(event) {
	gtm4wpEventClick();
});

/**
 * Detect clicks for data attribute & send info to dataLayer.
 */
function gtm4wpEventClick() {
	let items = document.querySelectorAll('[data-click-event]');

	if ( items ) {
		for (i of items) {
			i.addEventListener( 'click', function(e) {

				// Find specific DOM element from element parents.
				let findDOM = [ 'HEADER', 'FOOTER', 'ASIDE', 'SECTION' ];
				let parent = matchValue( getAncestors(this), findDOM );

				// Add click realted stuff to dataLayer.
				dataLayer.push({
					'event': 'agtm4wp_click',
					'wp_click_type': this.getAttribute('data-click-type'),
					'wp_click_event': this.getAttribute('data-click-event'),
					'wp_click_dom': parent
				});

			});
		}
	}
}

/**
 * Get all element ancestors nodeName.
 * @returns ancestors
 */
const getAncestors = el => {
	let ancestors = [];

	while (el) {
		el = el.parentNode;

		// Get only nodeNames from ancestors.
		if ( null !== el ){
			ancestors.unshift(el.nodeName);
		}
	}

	return ancestors;
};

/**
 * Detect if array contains specific values.
 *
 * @param array arr array where to find matches.
 * @param array find values to find.
 * @returns 
 */
function matchValue( arr, find ) {
	let domElement = '';

	find.forEach(value => {
		if ( arr.includes(value) ) {
			domElement = value	
		}
	});

	return domElement;
}