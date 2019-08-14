// Javascript Document
var Smartbox = Class.create();
Smartbox.prototype = {

    // Let's initialize it
    initialize: function (shippingMethod, AjaxUrl) {
        this.shippingMethod = shippingMethod;
        this.AjaxUrl = AjaxUrl;
        this.terminalSelection = false;
    },
	observerCheckoutProgressMutation: function(){
		// select the target node
		var target = document.getElementById('checkout-progress-wrapper');
		// create an observer instance
		this.observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				//console.log(mutation);
				if(typeof smartbox != "undefined"){
					smartbox.observeShippingMethods();
					}
			});
		});
		// configuration of the observer:
		var config = {
			attributes: true,
			childList: true,
			characterData: true
		};
		// pass in the target node, as well as the observer options
		this.observer.observe(target, config);
		
		},
    // hide show Smartbox Terminal Listing
    observeShippingMethods: function() {
        // Check all shipping method for changes
        $$('[name="shipping_method"]').each(function(element) {
            Element.observe(element, 'change', function(event) {
                if(Event.findElement(event).value == this.shippingMethod) {

                    // Hide shipping address in progress area
                    if($('shipping-progress-opcheckout') != undefined) {
                        $('shipping-progress-opcheckout').hide();
                    }

                    $('smartbox-box').show();

                } else {

                    // Show shipping address in progress area
                    if($('shipping-progress-opcheckout') != undefined) {
                        $('shipping-progress-opcheckout').show();
                    }

                    $('smartbox-box').hide();

                }
            }.bind(this));
        }.bind(this));

        // Smartbox is selected when the step are loaded, just hide the shipping progress section
        if($$('[name="shipping_method"]:checked').first() != undefined) {

            // Determine which method is selected
            if($$('[name="shipping_method"]:checked').first().value == this.shippingMethod) {

                // Hide shipping address in the progress area
                if($('shipping-progress-opcheckout') != undefined) {
                    $('shipping-progress-opcheckout').hide();
                }

                $('smartbox-box').show();

            } else {

                // Show shipping address in the progress area
                if($('shipping-progress-opcheckout') != undefined) {
                    $('shipping-progress-opcheckout').show();
                }
                $('smartbox-box').hide();
            }

        }

    },

    // user hits submit
    handleSubmit: function() {

        $('smartbox-search-input').observe('keypress', function(e) {
            // user enter
            if(e.keyCode == 13) {

                // Make request
                this.getPositionFromAddress($('smartbox-search-input').value, $('smartbox-search-button'));

                if (e.preventDefault) {
                    e.preventDefault();
                }
                return false;
            }
        }.bind(this));

    },

    // current position from GeoLocation
    getCurrentPosition: function(buttonElement) {

        // Add loading class name
        if(buttonElement) {
            $(buttonElement).addClassName('loading').setAttribute('disabled', 'disabled');
        }

        // Call to GeoLocation API
        navigator.geolocation.getCurrentPosition(
            function(position) {

                // Update Terminals Listing
                this.findClosestTerminals(position.coords.latitude, position.coords.longitude, function() {

                    // Remove loading class
                    if(buttonElement) {
                        $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                    }

                });

            }.bind(this),
            function(error) {

                // Message the user
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        this.throwError('Please turn on your location settings for us to find a Smartbox terminal near you.  Or else you can enter the area or pin-code in the space provided.');
                        break;
                    default:
                        this.throwError('We\'re unable to automatically retrieve your location.');
                        break;
                }

                // Remove loading class
                if(buttonElement) {
                    $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                }

            }.bind(this)
        );

    },

    // Get position from address and find closest Terminals
    getPositionFromAddress: function(address, buttonElement) {

        // Check if the address is empty
        if(address == '') {
			this.throwError('Please enter area or pincode.');
            return false;
        }

        // Add loading class name to the button
        if(buttonElement) {
            $(buttonElement).addClassName('loading').setAttribute('disabled', 'disabled');
        }

        // New instance of the Geocoder
        geocoder = new google.maps.Geocoder();

        // Make request to the API
        geocoder.geocode({
            address: address
        }, function(results, status) {

            if(status == google.maps.GeocoderStatus.OK) {

                // Grab geo coding information from the model
                geoLocation = results.first().geometry.location;

                // Update the Terminals Listing
                this.findClosestTerminals(geoLocation.lat(), geoLocation.lng(), function() {

                    // Remove the loading class
                    if(buttonElement) {
                        $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                    }

                });
            }

        }.bind(this));

    },

    // Find closest terminals from user's latitude & longitude
    findClosestTerminals: function(lat, long, callback) {

        this.captureUserSelection();

        // Make a new ajax request to the server
        new Ajax.Request(
            this.AjaxUrl,
            {
                method: 'get',
                onSuccess: function(transport) {

                    // Verify we have some response text
                    if (transport && transport.responseJSON) {

                        // If we have JSON use it
                        response = transport.responseJSON;

                    } else if(transport && transport.responseText) {

                        // Parse as an object
                        try {
                            response = eval('(' + transport.responseText + ')');
                        }
                        catch (e) {
                            response = {};
                        }

                    }

                    // If the request was successful let's update!
                    if(response.success && response.html) {

                        // Update the DOM with our table
                        $('smartbox-table').innerHTML = response.html;

                        
                        

                    } else if(response.error) {

                        // Attempt to throw the error nicely
                        this.throwError(response.error);
                    }

                    // If there is a callback function defined call it
                    if(callback) {
                        callback();
                    }

                    // Restore any selection if user has made it
                    this.restoreUserSelection();

                }.bind(this),
                parameters: {'lat': lat,'long': long}
            }
        );

    },

    // Capture the current users selection to be restored after an operation
    captureUserSelection: function() {

        // Always set back to false
        this.terminalSelection = false;

        // Check that one of the radio buttons is checked
        if($$('[name=smartbox-terminal]:checked').first() != undefined) {
            this.terminalSelection = $$('[name=smartbox-terminal]:checked').first().value;
        }

    },

    // Restore users previous selection
    restoreUserSelection: function() {

        // Check there is a previously terminal selection
        if(this.terminalSelection) {

            // Check that the radio button still exists
            if($('terminal-' + this.terminalSelection) != undefined) {
                $('terminal-' + this.terminalSelection).checked = true;
            }

            // Unset the store selection
            this.terminalSelection = false;

        }

    },

    // Throw an error, attempt to make it pretty
    throwError: function(message) {

        // Verify the message DOM exists
        if($$('#smartbox-box ul.messages').first() != undefined) {

            // Pull out the message box
            messageBox = $$('#smartbox-box ul.messages').first();

            // Build our new mark up
            errorElement = new Element('li').update(new Element('span').update(message));

            // Retrieve the UL
            ul = messageBox.down('ul');

            // Remove any previous errors
            if(ul.down('li') != undefined) {
                ul.down('li').remove();
            }

            // Add in the message
            ul.insert(errorElement);

            // Show the message box
            messageBox.show();

            // Hide the message box after 20 seconds
            hideMessages = false;
            clearTimeout(hideMessages);
            hideMessages = setTimeout(function () {
                messageBox.hide();
            }, 20000);

        } else {
            alert(message);
        }

    }

};