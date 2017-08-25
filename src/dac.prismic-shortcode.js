import Api from './PrismicHelper';
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
	 * @param {string} name Form element name.
	 * @param {string} query Api query string.
	 * @param {object} editor The editor instance.
	 * @param {object} e Caught eventt.
	 */
	let isOpen = false;
	const dac_autocomplete = (name, query, editor, e) => {
		console.log(e.target);
		// Mute space and enter.
		if ([13, 32].indexOf(e.keyCode) >= 0) {
			return;
		}
		// List to populate with results.
		const itemList = document.createElement('ul');
		// Our target textfield.
		const textbox = e.target;
		textbox.setAttribute('autocomplete', false);
		// Our result promise.
		const result = api.fullTextQuery(query, textbox.value);
		Promise.resolve(result).then(result => {
			if (result && !isOpen) {
				// Attach result list.
				itemList.classList.add('dac-autocomplete--list');
				// Add calculated styles.
				itemList.style.left = textbox.style.left;
				itemList.style.top = textbox.style.height;
				itemList.style.width = textbox.style.width;
				textbox.parentNode.appendChild(itemList);
				isOpen = true;
				// Format results.
				for (let uid in result) {
					// Create list item.
					let li = document.createElement('li');
					let a = document.createElement('a');
					a.setAttribute('data-uid', uid);
					a.setAttribute('href', '#');
					a.setAttribute('tabindex', 1);
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
								itemList.parentNode.removeChild(itemList);
								isOpen = false;
								const params = editor.windowManager.getParams();
								params[name] = e.target.dataset.uid;
								editor.windowManager.setParams(params);
								textbox.focus();
								break;
							case 27:
								itemList.parentNode.removeChild(itemList);
								textbox.focus();
								break;
						}
					});

					// Append items.
					li.appendChild(a);
					itemList.appendChild(li);
					// Attach ket navigation.
					listKeyNav(itemList, textbox);
				}
			}
		});
	}

	/**
	 * List key navigation.
	 *
	 * @param {object} list The list dom node.
	 * @param {object} element The input dom node to attach the list on.
	 */
	const listKeyNav = (list, input) => {
		const first = list.firstChild;
		const last = list.lastChild;
		window.addEventListener('keyup', (e) => {
			switch (e.keyCode) {
				// Up key.
				case 38:
					if (document.activeElement === input || first.firstChild) {
						last.firstChild.focus();
					}
					else if (document.activeElement.parentElement.previousSibling) {
						document.activeElement.parentElement.previousSibling.focus();
					}
					break;
					// Down key.
				case 40:
					if (document.activeElement === input || last.firstChild) {
						first.firstChild.focus();
					}
					else if (document.activeElement.parentElement.nextSibling) {
						document.activeElement.parentElement.nextSibling.focus();
					}
					break;
			}
		});
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
						body: [
							{
								type: 'textbox',
								name: 'uid',
								label: 'UID',
							},
							{
								type: 'textbox',
								name: 'case_area',
								label: 'Area',
								onkeyup: (e) => {
									// Autocomplete handler.
									dac_autocomplete('case_area', 'my.case_area.area_name', editor, e);
								}
							},
							{
								type: 'textbox',
								name: 'organisation',
								label: 'Organisation',
								onkeyup: (e) => {
									// Autocomplete handler.
									dac_autocomplete('organisation', 'my.organisation.name', editor, e);
								}
							},
							{
								type: 'textbox',
								name: 'case_category',
								label: 'Category',
								onkeyup: (e) => {
									// Autocomplete handler.
									dac_autocomplete('case_category', 'my.case-category.name', editor, e);
								}
							},
							{
								type: 'textbox',
								name: 'build_year',
								label: 'Build year',
							},
							{
								type: 'textbox',
								name: 'tags',
								label: 'Tags',
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
							var shortLink = `[content-hub ${attributes.join(' ')}]`;
							editor.insertContent(shortLink);
							editor.windowManager.close();
						}
					});
				}
			});
		},
	});
	// Init plugin.
	tinymce.PluginManager.add( 'dac_shortcode', tinymce.plugins.MyButtons );
})();
