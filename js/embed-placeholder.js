(function() {

"use strict";

var placeholders = document.getElementsByClassName('embed-placeholder');
var el;
for (var i = 0; i < placeholders.length; i++) {
	setupPlaceholder(placeholders[i]);
}

function setupPlaceholder(el) {
	var isTouch = 'ontouchstart' in document.documentElement;
	var eventType = isTouch ? 'touchstart' : 'click';
	el.addEventListener('click', function(e) {
		var src = el.getAttribute('data-embed-src');
		var img = el.getElementsByTagName('img')[0];
		var width = img.offsetWidth;
		var height = img.offsetHeight;
		el.innerHTML = '<div class="iframe-wrapper">' +
		                 '<iframe width="' + width + '" ' +
		                         'height="' + height + '" ' +
		                         'frameborder="0" ' +
		                         'allowfullscreen="allowfullscreen" ' +
		                         'src="' + src + '"></iframe>' +
		               '</div>';
		el.className = 'playing embed-placeholder';
		el.firstChild.style.paddingTop = (100 * height / width) + '%';
		if (isTouch) {
			setTimeout(function() {
				var iframe = el.getElementsByTagName('iframe')[0];
				console.log(iframe);
				iframe.dispatchEvent(e);
			}, 2000);
		}
	}, false);
}

})();
