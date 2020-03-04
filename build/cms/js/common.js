var jsPath, rootPath;

(function() {
	// Get the path to the website root and the scripts folder.
	// The root path is used as an endpoint for AJAX requests.
	// The javascripts path may be used by plugins.
	jsPath = '/js';
	let scripts = document.getElementsByTagName('script');
	for (path of scripts) {
		let loc = path.src.indexOf('/common.js');
		if (loc > 0) {
			jsPath = path.src.substring(0, loc);
			rootPath = jsPath.substring(0, jsPath.lastIndexOf('/'));
			break;
		}
	};
})();
