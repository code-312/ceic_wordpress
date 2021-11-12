( function( $ ) {
	'use strict';

	$( window ).on( 'load', function() {

		$( '.is-menu a, .is-menu a svg' ).on( 'click', function( e ) {

			 // Cancels the default actions.
			e.stopPropagation();
			e.preventDefault();

			if ( 'static' === $( this ).closest('ul').css( 'position' ) ) {
				$( this ).closest('ul').css( 'position', 'relative' );
			}

			if ( $( this ).closest( '.is-menu-wrapper' ).length ) {
				if ( $( this ).closest('.is-menu').hasClass( 'sliding' ) || $( this ).closest('.is-menu').hasClass( 'full-width-menu' ) ) {
					$( this ).closest( '.is-menu-wrapper' ).addClass( 'is-expanded' );
				}
			}

			if ( $( this ).closest('.is-menu').hasClass( 'sliding' ) || $( this ).closest('.is-menu').hasClass( 'full-width-menu' ) ) {
				$( this ).closest('.is-menu').find( 'button.is-search-submit' ).hide();
				var is_menu_height = $( this ).closest('li.is-menu').outerHeight();
				is_menu_height = ( is_menu_height / 2 );
				$( this ).closest('.is-menu').find( 'form' ).css({
                    top: ( is_menu_height - 18 ) + "px"
                });
                $( this ).closest('.is-menu').find( '.search-close' ).css({
                    top: ( is_menu_height - 10 ) + "px"
                });
			}
			if ( $( this ).closest('.is-menu').hasClass( 'is-dropdown' ) ) {
				$( this ).closest('.is-menu').find( 'form' ).fadeIn();
			} else if ( $( this ).closest('.is-menu').hasClass( 'sliding' ) ) {
				$( this ).closest('.is-menu').find( 'form' ).animate( { 
					width: '310'
					}, function() {
						$( this ).closest('.is-menu').addClass( 'open' );
						$( this ).closest('.is-menu').find( 'button.is-search-submit' ).show();
				} );
			} else if ( $( this ).closest('.is-menu').hasClass( 'full-width-menu' ) ) {
				var menu_width = $( this ).closest('ul').outerWidth();
				if ( $( this ).closest( '.is-menu-wrapper' ).hasClass( 'is-expanded' ) ) {
					menu_width = $( window ).width();
					$( this ).closest('.is-menu').find( 'form' ).css( 'right', '-5px' );
					$( this ).closest('.is-menu').find( '.search-close' ).hide();
				} else {
				var menu_pos = $( this ).offset();
				if ( ! $( this ).closest('.is-menu').hasClass( 'is-first' ) && menu_pos.left < menu_width ) {
					menu_width = menu_pos.left;
					var menu_item_width = $( this ).closest('li').outerWidth();
					if ( menu_item_width > menu_width ) {
						menu_width = menu_item_width;
					}
				}
				}
				$( this ).closest('.is-menu').find( 'form' ).animate( { 
					width: menu_width+'px',
					}, function() {
						$( this ).closest('.is-menu').addClass( 'active-search' );
						$( this ).closest('.is-menu').addClass( 'open' );
						$( this ).closest('.is-menu').find( 'button.is-search-submit' ).show();
				} );
			} else if ( $( this ).closest('.is-menu').hasClass( 'popup' ) ) {
				$( '#is-popup-wrapper' ).fadeIn();
				$( '#is-popup-wrapper form input[type="text"], #is-popup-wrapper form input[type="search"]' ).focus();
			}
			if ( $( this ).closest('.is-menu').hasClass( 'sliding' ) || $( this ).closest('.is-menu').hasClass( 'full-width-menu' ) ) {
				$( this ).closest('.is-menu').find( 'form input[type="search"], form input[type="text"]' ).focus();
			}
			$(this).closest('.is-menu').find( 'form input[type="search"], form input[type="text"]' ).focus();
		} );

		$( '#is-popup-wrapper' ).on( 'click', function( e ) {
			if ( ! $(e.target).closest('form').length ) {
				$( '#is-popup-wrapper, .is-ajax-search-result, .is-ajax-search-details' ).fadeOut();
			}
		} );
		if ( typeof IvorySearchVars !== "undefined" &&  typeof IvorySearchVars.is_analytics_enabled !== "undefined" ) {
			var id = ( typeof IvorySearchVars.is_id !== "undefined" ) ? IvorySearchVars.is_id : 'Default';
			var label = ( typeof IvorySearchVars.is_label !== "undefined" ) ? IvorySearchVars.is_label : '';
			var category = ( typeof IvorySearchVars.is_cat !== "undefined" ) ? IvorySearchVars.is_cat : '';
			ivory_search_analytics( id, label, category );
		}

 		if ( window.matchMedia( '(max-width: 1024px)' ).matches ) {
 			$( '.is-menu a' ).attr( 'href', '' );
 		}
 		$( window ).resize( function() {
	 		if ( window.matchMedia( '(max-width: 1024px)' ).matches ) {
	 			$( '.is-menu a' ).attr( 'href', '' );
	 		}
		} );

	} );

	$( document ).keyup( function( e ) {
		if ( e.keyCode === 27 ) {
			$( '#is-popup-wrapper, .is-ajax-search-result, .is-ajax-search-details' ).hide();
		}
	} );

	$( '.is-menu form input[type="search"], .is-menu form input[type="text"]' ).on( 'click', function( e ) {
		 e.stopPropagation();
		return false;
	} );

	$( 'form.is-search-form, form.search-form' ).on( 'mouseover', function( e ) {
		if ( $( this ).next( ".is-link-container" ).length ){
            $( this ).append( $( this ).next( ".is-link-container" ).remove() );
		}
	} );

	$( window ).click( function( e ) {
		if ( 0 === e.button && 0 === $( e.target ).closest( '.is-search-input' ).length && 0 === $( e.target ).closest( '.is-search-submit' ).length && 0 === $( e.target ).closest( '.is-ajax-search-result' ).length && 0 === $( e.target ).closest( '.is-ajax-search-details' ).length ) {
			if ( $( '.is-menu' ).hasClass( 'open' ) ) {
				$( '.is-menu button.is-search-submit' ).hide();
				$( '.is-menu form' ).animate(
					{ width: '0' },
					400,
					function() {
						$( '.is-menu' ).removeClass( 'active-search' );
						$( '.is-menu' ).removeClass( 'open' );
						$( '.is-menu-wrapper' ).removeClass( 'is-expanded' );
					}
				);
				$( '.is-ajax-search-result, .is-ajax-search-details' ).hide();
			} else if ( $( '.is-menu' ).hasClass( 'is-dropdown' ) ) {
				$( '.is-menu form' ).fadeOut();
				$( '.is-ajax-search-result, .is-ajax-search-details' ).hide();
			}
		}
	});

} )( jQuery );

function ivory_search_analytics( id, label, category ) {
    try {
        // YOAST uses __gaTracker, if not defined check for ga, if nothing go null, FUN EH??
        var _ga = typeof __gaTracker == "function" ? __gaTracker : (typeof ga == "function" ? ga : false);
        var _gtag = typeof gtag == "function" ? gtag : false;
            if ( _gtag !== false ) {
                _gtag('event', 'Ivory Search - '+id, {
                    'event_label': label,
                    'event_category': category
                });
                return;
            }

            if ( _ga !== false ) {
                _ga('send', {
                    hitType: 'event',
                    eventCategory: category,
                    eventAction: 'Ivory Search - '+id,
                    eventLabel: label
                });
                //_ga( 'send', 'pageview', '/?s=' + encodeURIComponent( label ) + '&id=' + encodeURIComponent( id )+ '&result=' + encodeURIComponent( category ) );
            }

        } catch (error) {
        }
}