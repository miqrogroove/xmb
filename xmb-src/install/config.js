function switchEnableFields() {
	for (var i = 1; i < arguments.length; i++) {
		els = document.getElementsByName(arguments[i]);
		el = els[0];

		if(arguments[0] == 0) {
			el.disabled = true;
		} else {
			el.disabled = false;
		}
	}
}

function hideConfig(hide) {
	var el = document.getElementById('configDiv');

	if(hide) {
		if(el.style) {
			if(el.style.setProperty) {
				el.style.setProperty('display', 'none', '');
			} else {
				el.style.display = 'none';
			}
		} else {
			el.display = 'none';
		}
	} else {
		if(el.style) {
			if(el.style.removeProperty) {
				el.style.removeProperty('display');
			} else {
				el.style.display = '';
			}
		} else {
			el.display = '';
		}
	}
	return true;
}