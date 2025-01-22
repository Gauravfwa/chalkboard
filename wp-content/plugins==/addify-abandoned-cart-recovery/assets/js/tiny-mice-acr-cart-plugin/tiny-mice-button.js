(function() {
	// Load plugin specific language pack

	tinymce.create('tinymce.plugins.acrCart', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			var myListItems = ['first-name', 'last-name', 'full-name', 'cart-subtotal', 'coupon', 'coupon-expiry' ,'cart-table', 'recovery-button', 'recovery-link' ];

			var menuItems = [];
			tinymce.each(myListItems, function (myListItemName) {

				var item_text  = '';
				var item_value = '';

				switch ( myListItemName ) {
					case 'first-name':
						item_text  = 'First Name';
						item_value = '{first-name}';
						break;
					case 'last-name':
						item_text  = 'Last Name';
						item_value = '{last-name}';
						break;
					case 'full-name':
						item_text  = 'Full Name';
						item_value = '{full-name}';
						break;
					case 'cart-subtotal':
						item_text  = 'Cart Subtotal';
						item_value = '{cart-subtotal}';
						break;
					case 'cart-table':
						item_text  = 'Cart Table';
						item_value = '{cart-table}';
						break;
					case 'coupon':
						item_text  = 'Coupon';
						item_value = '{coupon}';
						break;
					case 'coupon-expiry':
						item_text  = 'Coupon Expiry';
						item_value = '{coupon-expiry}';
						break;
					case 'recovery-button':
						item_text  = 'Recovery Button';
						item_value = '{recovery-button}';
						break;  recovery-link
					case 'recovery-link':
						item_text  = 'Recovery Link';
						item_value = '{recovery-link}';
						break;
				}

				menuItems.push({
					text: item_text,
					onclick: function () {
						ed.insertContent(item_value);
					}
				});
			});


			// Register example button
			ed.addButton('acr_cart', {
				title : 'Abandoned Cart Elements',
				type: 'menubutton',
				image : url + '/img/set.png',
				menu: menuItems
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('acr_cart', n.nodeName == 'IMG');
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Addify Abandoned Cart Plugin',
				author : 'Addify',
				authorurl : 'https://addify.co/',
				infourl : 'https://addify.co/',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('acr_cart', tinymce.plugins.acrCart);
})();
