(function() {
	/* Register the buttons */
	tinymce.create('tinymce.plugins.MyButtons', {
		 init : function(editor, url) {
			   // Add button.
			  	editor.addButton( 'dac_content_hub', {
					title : 'Create content hub shortcode',
					// Button icon.
					image : plugin_dir + '/img/dac.svg',
					onclick : function() {
						// Open dialog with filter fields.
						editor.windowManager.open({
							title: 'Content hub',
							body: [
								{
									type: 'textbox',
									name: 'uid',
									label: 'UID'
								},
								{
									type: 'textbox',
									name: 'case_area',
									label: 'Area'
								},
								{
									type: 'textbox',
									name: 'organisation',
									label: 'Organisation'
								},
								{
									type: 'textbox',
									name: 'case_category',
									label: 'Category'
								},
								{
									type: 'textbox',
									name: 'build_year',
									label: 'Build year'
								}
							],
							// Submit handler.
							onsubmit: function (e) {
								var attributes = [];
								// Create attrubutes from field values.
								for (var i in e.data ) {
									if (e.data[i]) {
										attributes.push(`${i}="${e.data[i]}"`);
									}
								}
								// Build shortlink and insert.
								var shortLink = `[content-hub ${attributes.join(' ')}]`;
								editor.insertContent(shortLink);
								editor.windowManager.close();
							}
						});
				   }
			  });
		 }
	});
	// Init plugin.
	tinymce.PluginManager.add( 'dac_shortcode', tinymce.plugins.MyButtons );
})();
