import Api from './PrismicHelper';
(($) => {
	// Get settings.
	const settings = window.dac_vars || null ;

	// Abort if no settings.
	if (!settings) {
		return;
	}
	// Init Api.
	const api = new Api;
	// Autocomplete handler.
	const dac_autocomplete = (name, query, editor, e) => {
		// List to populate with results.
		const itemList = document.createElement('ul');
		// Our target textfield.
		const textbox = e.target;
		// Our result promise.
		const result = api.fullTextQuery(query, textbox.value);
		Promise.resolve(result).then(result => {
			if (result) {
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
					let elem = document.createElement('li');
					elem.setAttribute('data-uid', uid)
					const value = result[uid];
					const text = document.createTextNode(value);
					elem.appendChild(text);
					elem.style.cursor = 'pointer';
					// Click handler for result.
					elem.addEventListener('click', (e) => {
						textbox.value = value;
						console.dir(textbox);
						itemList.parentNode.removeChild(itemList);
						// Set to tinyMce paramaters.
						const params = editor.windowManager.getParams();
						params[name] = e.target.dataset.uid;
						editor.windowManager.setParams(params);
					});
					itemList.appendChild(elem);
				}
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
								onkeyup: (e) => {let results;
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
})(jQuery);
