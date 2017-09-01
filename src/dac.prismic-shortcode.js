import Api from './PrismicHelper';
import throttle from 'lodash.throttle';

(() => {
	// Get settings.
	const settings = window.dac_vars || null ;

	// Abort if no settings.
	if (!settings) {
		return;
	}
	// Init Api.
	const api = new Api;

	/**
	 * Autocomplete handler.
	 *
	 * @param {objec} e Event.
	 */
	let isOpen = false;
	const dacAutocomplete = (e, editor) => {
		const itemList = document.createElement('ul');
		// Our target textfield.
		const textbox = e.target;
		// Add throttled key event.
		textbox.addEventListener('keypress', throttle((e) => {
			// Mute some keys.
			if ([9, 13, 32].indexOf(e.keyCode) >= 0) {
				return;
			}
			// Do not continute if input is less than 2 characters.
			if (textbox.value.length < 2) {
				return;
			}
			// Get type and query.
			let options = {};
			if (textbox.parentNode.classList.contains('mce-dac-input--area')) {
				options.name = 'case_area';
				options.type = 'case_area';
				options.query = 'my.case_area.area_name'
			}
			if (textbox.parentNode.classList.contains('mce-dac-input--organisation')) {
				options.name = 'organisation';
				options.type = 'organisation';
				options.query = 'my.organisation.name';
			}
			if (textbox.parentNode.classList.contains('mce-dac-input--category')) {
				options.name = 'case_category';
				options.type = 'case-category';
				options.query = 'my.case-category.name';
			}
			if (Object.keys(settings).length <= 0) {
				return;
			}
			// Get name .
			const name = options.name;
			// Loader.
			textbox.classList.add('loading');

			// Our result promise.
			const result = api.fullTextQuery(options, textbox.value);
			Promise.resolve(result).then(result => {
				textbox.classList.remove('loading');
				if (result && !isOpen) {
					isOpen = true;
					// Attach result list.
					itemList.classList.add('dac-autocomplete--list');
					// Add calculated styles.
					itemList.style.left = textbox.style.left;
					itemList.style.top = textbox.style.height;
					itemList.style.width = textbox.style.width;
					textbox.parentNode.appendChild(itemList);
					// Format results.
					for (let uid in result) {
						// Create list item.
						let li = document.createElement('li');
						let a = document.createElement('a');
						a.setAttribute('data-uid', uid);
						a.setAttribute('href', '#');
						a.setAttribute('tabindex', -1);
						const value = result[uid];
						const text = document.createTextNode(value);
						a.appendChild(text);

						// Click handler.
						a.addEventListener('click', (e) => {
							e.preventDefault();
							textbox.value = value;
							itemList.parentNode.removeChild(itemList);
							isOpen = false;
							const params = editor.windowManager.getParams();
							params[name] = e.target.dataset.uid;
							editor.windowManager.setParams(params);
							textbox.focus();
						});
						// Keydown handler.
						a.addEventListener('keyup', (e) => {
							switch (e.keyCode) {
								case 13:
								case 32:
									textbox.value = value;
									itemList.innerHTML = "";
									isOpen = false;
									const params = editor.windowManager.getParams();
									params[name] = e.target.dataset.uid;
									editor.windowManager.setParams(params);
									textbox.focus();
									break;
								case 27:
									e.preventDefault();
									isOpen = false;
									itemList.innerHTML = "";
									textbox.focus();
									break;
							}
						});

						// Append items.
						li.appendChild(a);
						itemList.appendChild(li);
					}
				}
			});
		}, 1000));

		// Clear list.
		e.target.addEventListener('blur', (e) => {
			if (!isOpen) {
				itemList.innerHTML = "";
			}
			//
		});

		// Attach key navigation.
		window.addEventListener('keydown', (e) => {
			listKeyNav(itemList, textbox, e);
		});
	}

	/**
	 * List key navigation.
	 *
	 * @param {object} list The list dom node.
	 * @param {object} element The input dom node to attach the list on.
	 */
	const listKeyNav = (list, input, e) => {
		// Abort if no children.
		if (list.childElementCount <= 0) {
			return;
		}

		const first = list.firstChild;
		const last = list.lastChild;
		const activeElement = e.target;

		switch (e.keyCode) {
			// Down key.
			case 40:
				e.preventDefault();
				e.stopPropagation();
				if ((input === activeElement) || (last.firstChild === activeElement)) {
					first.firstChild.focus();
				}
				else if (('A' === activeElement.tagName) && (null !== activeElement.parentNode.nextSibling)) {
					activeElement.parentNode.nextSibling.firstChild.focus();
				}
				else {
					return;
				}
				break;
			// Up key.
			case 38:
				e.preventDefault();
				e.stopPropagation();
				if ((input === activeElement) || (first.firstChild === activeElement)) {
					last.firstChild.focus();
				}
				else if (('A' === activeElement.tagName) && (null !== activeElement.parentNode.previousSibling)) {
					activeElement.parentNode.previousSibling.firstChild.focus();
				}
				else {
					return;
				}
				break;
		}
	}

	// Create buttons.
	tinymce.create('tinymce.plugins.MyButtons', {
		init: (editor, url) => {
			// Add button.
			editor.addButton( 'dac_content_hub', {
				title: 'Create content hub shortcode',
				// Button icon.
				image: settings.plugin_dir + '/img/dac.svg',
				onclick: () => {
					// Open dialog with filter fields.
					editor.windowManager.open({
						title: 'Content hub',
						type: 'container',
						layout: 'flex',
						body: [
							{
								type: 'combobox',
								name: 'uid',
								placeholder: 'UID',
								classes: 'dac-input dac-input--uid'
							},
							{
								type: 'combobox',
								name: 'case_area',
								placeholder: 'Area',
								classes: 'dac-input dac-input--area dac-autocomplete'
							},
							{
								type: 'combobox',
								name: 'organisation',
								placeholder: 'Organisation',
								classes: 'dac-input dac-input--organisation dac-autocomplete'
							},
							{
								type: 'combobox',
								name: 'case_category',
								placeholder: 'Category',
								classes: 'dac-input dac-input--category dac-autocomplete'
							},
							{
								type: 'combobox',
								name: 'build_year',
								placeholder: 'Build year',
								classes: 'dac-input dac-input-year'
							},
							{
								type: 'combobox',
								name: 'tags',
								placeholder: 'Tags',
								classes: 'dac-input dac-input--tags'
							}
						],
						// Submit handler.
						onsubmit: (e) => {
							var attributes = [];
							const params = Object.assign(Object(e.data), editor.windowManager.getParams());
							// Create attrubutes from field values.
							for (var i in params) {
								if (params[i]) {
									attributes.push(`${i}="${params[i]}"`);
								}
							}
							// Build short code and insert.
							var shortCode = `[content-hub ${attributes.join(' ')}]`;
							editor.insertContent(shortCode);
							editor.windowManager.close();
						}
					});

					// Add event listeners on autocomplete elements.
					for (let elem of document.querySelectorAll('.mce-dac-autocomplete input')) {
						const focusHandler = (e) => {
							// We need to pass the editor object.
							dacAutocomplete(e, editor);
						}
						elem.addEventListener('focus', focusHandler, {once: true});
					}
				}
			});
		},
	});
	// Init plugin.
	tinymce.PluginManager.add( 'dac_shortcode', tinymce.plugins.MyButtons );
})();
