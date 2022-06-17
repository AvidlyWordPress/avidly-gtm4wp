// Run when DOM is loaded.
document.addEventListener('DOMContentLoaded', function(event) {
	gtm4wpEventClick();
})

/**
 * Detect clicks for data attribute & send info to dataLayer.
 */
function gtm4wpEventClick() {
	let items = document.querySelectorAll('[data-click-event]');

	if ( items ) {
		for (i of items) {
			i.addEventListener( 'click', function(e) {
				dataLayer.push({
					'wp_click_type': this.getAttribute('data-click-type'),
					'wp_click_event': this.getAttribute('data-click-event')
				});
			});
		}
	}
}