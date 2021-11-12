( function( $ ) {

	'use strict';

	if ( typeof ivory_search === 'undefined' || ivory_search === null ) {
		return;
	}

	$( function() {

		$( window ).on( 'load', function() {
			$( '.is-cb-dropdown .is-cb-multisel' ).css({ 'position': 'absolute', 'display': 'none' });
			$( '.col-wrapper .load-all' ).on( 'click', function() {
				var post_id = $('#post_ID').val();
				var post_type = $(this).attr('id');
				var this_load = $(this);
				var inc_exc = $('.search-form-editor-panel').attr('id');
				$(this).parent().append('<span class="spinner"></span>');
				$.ajax( {
					type : "post",
					url: ivory_search.ajaxUrl,
					data: {
						action: 'display_posts',
						post_id: post_id,
						post_type: post_type,
						inc_exc: inc_exc
					},
					success: function( response ) {
						$(this_load).parent().find('select').find('option').remove().end().append(response );
						if ( $(this_load).parent().find('select option:selected').length ) {
							$(this_load).parent().find('.col-title span').html( '<strong>'+$(this_load).parent().find('.col-title').text()+'</strong>');
						}
						$(this_load).parent().find('.spinner').remove();
						$(this_load).remove();
					},
					error: function (request, error) {
						alert( " The posts could not be loaded. Because: " + error );
					}
				} );
			} );

		} );

			$( document ).ready( function() {
						var url = window.location.href;
						var args = url.split('=').pop();
						if ( 'menu-search' === args ) {
							$('#toplevel_page_ivory-search .wp-submenu-wrap li, #toplevel_page_ivory-search .wp-submenu-wrap li a').removeClass('current');
							$('#toplevel_page_ivory-search .wp-submenu-wrap li:nth-child(3), #toplevel_page_ivory-search .wp-submenu-wrap li:nth-child(3) a').addClass('current');
						}
			} );

            var dateFormat = "d-m-yy",
                  from = $( "#is-after-datepicker" )
                    .datepicker({
                    dateFormat : 'd-m-yy',
                    changeMonth: true,
                    changeYear: true
                    })
                    .on( "change", function() {
                      to.datepicker( "option", "minDate", isgetDate( this ) );
                    }),
                  to = $( "#is-before-datepicker" ).datepicker({
                    dateFormat : 'd-m-yy',
                    changeMonth: true,
                    changeYear: true
                  })
                  .on( "change", function() {
                    from.datepicker( "option", "maxDate", isgetDate( this ) );
                  });

                function isgetDate( element ) {
                  var date;
                  try {
                    date = $.datepicker.parseDate( dateFormat, element.value );
                  } catch( error ) {
                    date = null;
                  }
                  return date;
                }

		if ( 1 < $(".form-table h3[scope=row]").length ) {
		$('.form-table .is-actions a.expand').click( function() {
			$('.form-table .is-actions a.expand').hide();
			$('.form-table .ui-accordion-header:not(.is-ptype-hidden), .form-table .ui-accordion-content:not(.is-ptype-hidden), .form-table .is-actions a.collapse').show();
			$('.form-table .ui-accordion-content').addClass('ui-accordion-content-active');
			$('.form-table h3').addClass('ui-state-active').removeClass('ui-accordion-header-active');
            if ( history.pushState ) {
                var newurl = window.location.href.split('#')[0]+ '#expand';
                window.history.pushState({path:newurl},'',newurl);
                $('form input[name="_wp_http_referer"]').val( $('form input[name="_wp_http_referer"]').val().split('#')[0]+ '#expand');
                if ( $('#is-admin-form-element').length ) {
                $('#is-admin-form-element').attr( 'action', $('#is-admin-form-element').attr('action').split('#')[0]+ '#expand');
                }
            }
			return false;
		} );
		
		$('.form-table .is-actions a.collapse').click( function() {
			$('.form-table .is-actions a.expand').show();
			$('.form-table .ui-accordion-content, .form-table .is-actions a.collapse').hide();
			$('.form-table .ui-accordion-content').removeClass('ui-accordion-content-active');
			$('.form-table h3').removeClass('ui-state-active');
            if ( history.pushState ) {
                var newurl = window.location.href.split('#')[0]+ '#collapse';
                window.history.pushState({path:newurl},'',newurl);
                $('form input[name="_wp_http_referer"]').val( $('form input[name="_wp_http_referer"]').val().split('#')[0]+ '#collapse');
                if ( $('#is-admin-form-element').length ) {
                $('#is-admin-form-element').attr( 'action', $('#is-admin-form-element').attr('action').split('#')[0]+ '#collapse');
                }
            }
			return false;
		} );

        var accordion_id = window.location.href.split('#');
        if ( 2 === accordion_id.length ) {
            $('form input[name="_wp_http_referer"]').val( $('form input[name="_wp_http_referer"]').val().split('#')[0]+ '#'+accordion_id[1]);
            if ( $('#is-admin-form-element').length ) {
            $('#is-admin-form-element').attr( 'action', $('#is-admin-form-element').attr('action').split('#')[0]+ '#'+accordion_id[1]);
            }
        }

		$( ".form-table" ).accordion( {
			collapsible: true,
			heightStyle: "content",
			icons: false,
			active: false,
            create: function( event, ui ) {
            if ( 2 === accordion_id.length ) {
                var temp_id = accordion_id[1].split('-');
                if ( 3 === temp_id.length ) {
                    temp_id = ( temp_id[2] / 2 ) - 1;
                   	$(".form-table").accordion( "option", "active", temp_id );

                    $('html, body').animate({
                            scrollTop: ( ( $('#'+accordion_id[1]).offset().top ) - 200 )
                          }, 500 );
                } else {
                    switch( accordion_id[1] ) {
                        case 'expand':
                            $('.form-table .is-actions a.expand').hide();
                            $('.form-table .ui-accordion-content, .form-table .is-actions a.collapse').show();
                            $('.form-table .ui-accordion-content').addClass('ui-accordion-content-active');
                            $('.form-table h3').addClass('ui-state-active').removeClass('ui-accordion-header-active');
                        break;
                        case 'collapse':
                            $('.form-table .is-actions a.expand').show();
                            $('.form-table .ui-accordion-content, .form-table .is-actions a.collapse').hide();
                            $('.form-table .ui-accordion-content').removeClass('ui-accordion-content-active');
                            $('.form-table h3').removeClass('ui-state-active');
                        break;
                    }
                }
            } else {
            	if ( 1 < $(".form-table h3[scope=row]").length ) {
            		$(".form-table").accordion( "option", "active", false );
            	} else {
                    $(".form-table").accordion( "option", "active", 0 );
                }
                
            }
            },
		} );

        $('.form-table h3').click( function() {
            var aria_controls = $( this ).attr('aria-controls');
            if ( history.pushState ) {
                var newurl = window.location.href.split('#')[0]+ '#'+aria_controls;
                window.history.pushState({path:newurl},'',newurl);
                $('form input[name="_wp_http_referer"]').val( $('form input[name="_wp_http_referer"]').val().split('#')[0]+ '#'+aria_controls );
                if ( $('#is-admin-form-element').length ) {
                $('#is-admin-form-element').attr( 'action', $('#is-admin-form-element').attr('action').split('#')[0]+ '#'+aria_controls);
                }
            }
        } );

		}

		$('.is-colorpicker').wpColorPicker();

		$('#search-body select[multiple] option').mousedown(function(e) {
			if ($(this).attr('selected')) {
				$(this).removeAttr('selected');
				return false;
			}
		} );

		$( ".col-title .list-search" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		$( ".list-search.wide" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		if ( '' === $( '#title' ).val() ) {
			$( '#title' ).focus();
		}

                if ( 0 !== $( '#title' ).length ) {
                    ivory_search.titleHint();
                }

		var changed = false;

		$(document).on("submit", "form", function(event){
			changed = false;
			$(window).off('beforeunload');
		} );

		$( window ).on( 'beforeunload', function( event ) {

			$( '#search-body :input[type!="hidden"]' ).each( function() {
				if ( ! $( this ).hasClass('wp-color-result') ){
				if ( $( this ).is( ':checkbox, :radio' ) ) {
					if ( this.defaultChecked != $( this ).is( ':checked' ) ) {
						changed = true;
					}
				} else if ( $( this ).is( 'select' ) ) {
					$( this ).find( 'option' ).each( function() {
						if ( this.defaultSelected != $( this ).is( ':selected' ) && '0' != $( this ).val() && 'Default Search Form' != $( this ).text() ) {
							changed = true;
						}
					} );
				} else {
					if ( this.defaultValue != $( this ).val() ) {
						changed = true;
					}
				}
				}
			} );

			if ( changed ) {
				event.returnValue = ivory_search.saveAlert;
				return ivory_search.saveAlert;
			}
		} );

		$( '#is-admin-form-element' ).submit( function() {
			if ( 'copy' != this.action.value ) {
				$( window ).off( 'beforeunload' );
			}

			if ( 'save' == this.action.value ) {
				$( '#publishing-action .spinner' ).addClass( 'is-active' );
			}
		} );

		// Tooltip only Text
		$('#search-body #titlewrap').hover(function(){
			if($(this).find("#title[disabled]").length){
			// Hover over code
			var title = $(this).find("#title[disabled]").attr('title');
			$(this).find("#title[disabled]").data('tipText', title).removeAttr('title');
			$('<p class="title_tooltip"></p>')
			.text(title)
			.appendTo('body');
			}
		}, function() {
			// Hover out code
			$(this).find("#title[disabled]").attr('title', $(this).find("#title[disabled]").data('tipText'));
			$('.title_tooltip').remove();
		}).mousemove(function(e) {
			var mousex = e.pageX + 20; //Get X coordinates
			var mousey = e.pageY - 40; //Get Y coordinates
			$('.title_tooltip')
			.css({ top: mousey, left: mousex })
		});

		$('#search-form-editor-tabs li').hover(function(){
			// Hover over code
			var title = $(this).find("a").attr('title');
			$(this).find("a").data('tipText', title).removeAttr('title');
			$('<p class="title_tooltip '+$(this).attr('id')+'"></p>')
			.text(title)
			.appendTo('body')
			.fadeIn('slow');
		}, function() {
			// Hover out code
			$(this).find("a").attr('title', $(this).find("a").data('tipText'));
			$('.title_tooltip.'+$(this).attr('id')).remove();
		}).mousemove(function(e) {
			var mousex = e.pageX + 20; //Get X coordinates
			var mousey = e.pageY - 40; //Get Y coordinates
			$('.title_tooltip.'+$(this).attr('id'))
			.css({ top: mousey, left: mousex })
		});

	} );

	ivory_search.titleHint = function() {
		var $title = $( '#title' );
		var $titleprompt = $( '#title-prompt-text' );

		if ( '' === $title.val() ) {
			$titleprompt.removeClass( 'screen-reader-text' );
		}

		$titleprompt.click( function() {
			$( this ).addClass( 'screen-reader-text' );
			$title.focus();
		} );

		$title.blur( function() {
			if ( '' === $(this).val() ) {
				$titleprompt.removeClass( 'screen-reader-text' );
			}
		} ).focus( function() {
			$titleprompt.addClass( 'screen-reader-text' );
		} ).keydown( function( e ) {
			$titleprompt.addClass( 'screen-reader-text' );
			$( this ).unbind( e );
		} );
	};

        $(".is-cb-dropdown .is-cb-title").on('click', function() {
            if ( $( this ).hasClass('is-dropdown-toggle') ){
                $( this ).removeClass('is-dropdown-toggle');
            } else {
                $( this ).addClass('is-dropdown-toggle');
            }
          $( this ).parents(".is-cb-dropdown").find(".is-cb-multisel").slideToggle('fast');
        });

        $(document).bind('click', function(e) {
          var $clicked = $(e.target);
          if (!$clicked.parents().hasClass("is-cb-dropdown")) {
              $(".is-cb-dropdown .is-cb-multisel").hide();
              $( '.is-cb-title' ).removeClass('is-dropdown-toggle');
          }
        });

        $('.is-cb-multisel input[type="checkbox"]').on('click', function() {

          var title = $(this).val();
          var title2 = $(this).parent().text().trim();

          if ($(this).is(':checked')) {
            var html = '<span title="' + title + '"> ' + title2 + '</span>';
            $( this ).parents(".is-cb-dropdown").find('.is-cb-titles').append(html);
            $( this ).parents(".is-cb-dropdown").find(".is-cb-select").hide();
            $( '.form-table h3.post-type-'+title ).show();
            $( '.form-table .post-type-'+title ).show().removeClass('is-ptype-hidden');
          } else {
            $( '.form-table .post-type-'+title ).hide().addClass('is-ptype-hidden');
			$( '.form-table .post-type-'+title+' #'+title+'-post-search_all' ).trigger( 'click' );
            $( this ).parents(".is-cb-dropdown").find('.is-cb-titles span[title="' + title + '"]').remove();
            if ( 0 === $( this ).parents(".is-cb-dropdown").find( '.is-cb-titles span' ).length ) {
                $( this ).parents(".is-cb-dropdown").find(".is-cb-select").show();
            }
          }
        });

        $( '#search-form-editor .is-mime-select, #search-form-editor .is-post-select, #search-form-editor .is-tax-select, #search-form-editor .is-meta-select' ).each( function() {
            if ( $( this ).is( ':checked' ) ) {
                if ( 'all' === $( this ).val() ) {
                    if ( $( this ).hasClass( 'is-post-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-posts' ).hide();
                    } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-taxes' ).hide();
                    } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-mime' ).hide();
                    }
                }
                if ( $( this ).hasClass( 'is-meta-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).show();
                }
            } else {
                if ( $( this ).hasClass( 'is-meta-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).hide();
                }                
            }
        } );

        $( '#search-form-editor .is-post-select, #search-form-editor .is-tax-select, #search-form-editor .is-meta-select, #search-form-editor .is-mime-select' ).on( 'click', function(e) {
			// Cancels the default actions.
			e.stopPropagation();  
            if ( $( this ).hasClass( 'is-meta-select' ) ) {
                if ( $( this ).is( ':checked' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).show();
                } else {
                    $( this ).closest( 'div' ).find( '.is-metas' ).hide();
                    $( this ).closest( 'div' ).find( '.is-metas select option').prop( 'selected', false );
                }
            } else if ( 'selected' === $( this ).val() ) {
                if ( $( this ).hasClass( 'is-post-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-posts' ).show();
                    $( this ).closest( 'div' ).find( '.notice-is-info' ).hide();
                } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-taxes' ).show();
                } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-mime' ).show();
                }
            } else {
                if ( $( this ).hasClass( 'is-post-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-posts' ).hide();
					$( this ).closest( 'div' ).find( '.notice-is-info' ).show();
                    $( this ).closest( 'div' ).find( '.is-posts select option').prop( 'selected', false );
                } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-taxes' ).hide();
                    $( this ).closest( 'div' ).find( '.is-taxes select option').prop( 'selected', false );
                } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-mime' ).hide();
                    $( this ).closest( 'div' ).find( '.is-mime select option').prop( 'selected', false );
                }
            }
        } );


        $('.includes_extras input[type="checkbox"]').on('click', function() {

          if ( ! $(this).is(':checked') ) {
              if ( ! $( this ).closest( '.includes_extras' ).find( 'input[type="checkbox"]:checked' ).length ) {
                  alert('Please make sure that you have configured the search form to search any content');
              }
          }
        } );

        $( '#search-form-editor .is-mime option' ).on( 'click', function() {
            if ( ! $( this ).is(':checked') ) {
                if ( 0 === $('.is-mime select option:selected').length ) {
                    if ( $('#includes').length ) {
                        $('.search-attachments').prop( "checked", true );
                    } else {
                        $('.search-attachments').prop( "checked", false );
                    }
                }
            } else {
                is_mime_multi_option_selected();
            }
        } );

        $( '#search-form-editor .is-mime-select' ).on( 'click', function() {
            if ( 'all' === $( this ).val() ) {
                if ( $('#excludes').length ) {
                    $('.search-attachments').prop( "checked", false );
                } else {
                    $('.search-attachments').prop( "checked", true );
                }
            }
        } );

        if ( 0 !== $('.is-mime select option:selected').length ) {
            is_mime_multi_option_selected();
        }

        function is_mime_multi_option_selected(){
                var temp_value = ['image', 'video', 'audio', 'text', 'pdf'];
                $.each(temp_value, function(key, value) {
                if ( 0 === $('.is-mime select option[value*="'+value+'"]:selected').length ) {
                    $('.search-attachments[name*="'+value+'"]').prop( "checked", false );
                } else {
                    $('.search-attachments[name*="'+value+'"]').prop( "checked", true );
                }
                } );

                if ( 0 === $('.is-mime select option[value*="doc"]:selected').length && 0 === $('.is-mime select option[value*="excel"]:selected').length && 0 === $('.is-mime select option[value*="word"]:selected').length ) {
                    $('.search-attachments[name*="doc"]').prop( "checked", false );
                } else {
                    $('.search-attachments[name*="doc"]').prop( "checked", true );
                }            
        }

        $( '.search-attachments-wrapper' ).show();

        $('.search-attachments').on('click', function() {
            if ( 0 === $('.is-mime select option:selected').length ) {
                if ( ! $(this).hasClass('exclude') ){
                if ( $( this ).is(':checked') ) {
                    return;
                } else {
                    alert('You can configure it to exclude from search in the search form Exclude section');
                    return false;
                }
                }
            }
            var search_name = $( this ).attr('name');
            var search_value = '';

            switch(search_name) {
              case 'search_images':
                  search_value = 'image';
                break;
              case 'search_videos':
                  search_value = 'video';
                break;
              case 'search_audios':
                  search_value = 'audio';
                break;
              case 'search_text':
                  search_value = 'text';
                break;
              case 'search_pdfs':
                  search_value = 'pdf';
                break;
              case 'search_docs':
                  search_value = ['doc', 'excel', 'word'];
                break;
            }
            if ( '' !== search_value ) {
                if ( Array.isArray( search_value ) ) {
                    var this2 = this;
                    $.each(search_value, function(key, value) {
                    if ( $( this2 ).is(':checked') ) {
                        $('.is-mime select option[value*="'+value+'"]').attr('selected', 'selected');
                    } else {
                        $('.is-mime select option[value*="'+value+'"]').removeAttr('selected');
                    }
                    });
                } else {
                if ( $( this ).is(':checked') ) {
                    $('.is-mime select option[value*="'+search_value+'"]').attr('selected', 'selected');
                } else {
                    $('.is-mime select option[value*="'+search_value+'"]').removeAttr('selected');
                }
                }
            }
                if ( ! $(this).hasClass('exclude') && 0 === $('.is-mime select option:selected').length ) {
                    $('.search-attachments').prop( "checked", true );
                }
        } );

        $( '#search-form-editor .post-type-attachment .is-posts option' ).on( 'click', function() {
            if ( 0 === $('#search-form-editor .post-type-attachment .is-posts option:selected').length ) {
				$( '.is-mime-radio, .search-attachments-wrapper' ).show();            	
            } else {
            	$( '.is-mime-radio, .is-mime, .search-attachments-wrapper' ).hide();
            }
        } );

        $('#search-form-editor .not_load_files').each(function() {
            if( ! $( this ).is(':checked') ) {
                $( this ).parent().next('.not-load-wrapper').hide();
            }
        } );

        $('#search-form-editor .not_load_files').on('click', function() {
            if( $( this ).is(':checked') ) {
                $( this ).parent().next('.not-load-wrapper').show();
            } else {
                $( this ).parent().next('.not-load-wrapper').hide();
            }
        } );

	function toggle_highlight_color_inputs() {
		if( $( '._is_settings-highlight_terms' ).is(':checked') ) {
			$( '.highlight-container' ).removeClass('is-field-disabled').show();
		} else {
			$( '.highlight-container' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_highlight_color_inputs();
	$( '._is_settings-highlight_terms' ).on( 'click', function() {
		toggle_highlight_color_inputs();
	} );

	function toggle_menu_search_inputs() {
		if( $( '.ivory_search_locations' ).is(':checked') || $( '.ivory_search_menu_name' ).is(':checked') ) {
			$( '.menu-settings-container' ).removeClass('is-field-disabled').show();
		} else {
			$( '.menu-settings-container' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_menu_search_inputs();
	$( '.ivory_search_locations, .ivory_search_menu_name' ).on( 'click', function() {
		toggle_menu_search_inputs();
	} );

	function toggle_menu_style_inputs( style ) {
		if( ( undefined !== style && 'default' !== style ) || ! $('#is_menu_styledefault').is(":checked") ) {
			$( '.form-style-dependent' ).removeClass('is-field-disabled').show();
		} else {
			$( '.form-style-dependent' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_menu_style_inputs();
	$( '.ivory_search_style' ).on( 'click', function() {
		toggle_menu_style_inputs($(this).val());
	} );

	function toggle_description_inputs() {
		if( $( '#_is_ajax-show_description' ).is(':checked') ) {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_description_inputs();
	$( '._is_ajax-description_wrap .check-radio' ).on( 'click', function() {
		toggle_description_inputs();
	} );

	function toggle_details_box_fields() {
		if( $( '#_is_ajax-show_details_box' ).is(':checked') && ( ( $( '#_is_ajax-show_matching_categories' ).is(':checked') || $( '#_is_ajax-show_matching_tags' ).is(':checked') ) ) ) {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).addClass('is-field-disabled').hide();
		}
	}
        toggle_details_box_fields();
	$( '#_is_ajax-show_details_box, #_is_ajax-show_matching_categories, #_is_ajax-show_matching_tags' ).on( 'click', function() {
                    toggle_details_box_fields();
	} );

	function toggle_show_more_result_textbox_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap, ._is_ajax-show_more_func_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap, ._is_ajax-show_more_func_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_more_result_textbox_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_show_more_result_textbox_fields();
	} );

	function toggle_show_view_all_textbox_fields() {
		if( $( '#_is_ajax-view_all_results' ).is(':checked') ) {
			$( '._is_ajax-view_all_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-view_all_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_view_all_textbox_fields();
	$( '._is_ajax-view_all_results_wrap .check-radio' ).on( 'click', function() {
		toggle_show_view_all_textbox_fields();
	} );

	function toggle_more_result_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_more_result_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_more_result_fields();
	} );

	// Enable Customize Fields.
	function toggle_enable_customize() {
		if( $( '#_is_customize-enable_customize' ).is(':checked') ) {
			$( '.form-table-panel-customize .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-customize .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	toggle_enable_customize();

	// Displays customizer enable confirmation alert.
	function enable_customizer_alert() {
        window.setTimeout(function () {
			var r = confirm("A page reload is required for this change.");
			if ( r == true ) {
				toggle_enable_customize();
				$('#is-admin-form-element').submit();
			} else {
				if( $( '#_is_customize-enable_customize' ).is(':checked') ) {
					$('#_is_customize-enable_customize').prop('checked', false);
				} else {
					$('#_is_customize-enable_customize').prop('checked', true);
				}
			}
        }, 300);
	}
	
	$( '#_is_customize-enable_customize' ).on( 'click', function() {
		enable_customizer_alert();
	} );

	$( '.form-table-panel-customize .is-field-disabled-message .message' ).on( 'click', function() {
		$( '#_is_customize-enable_customize' ).trigger('click');

	} );

	// Enable AJAX.
	function toggle_enable_ajax() {
		if( $( '#_is_ajax-enable_ajax' ).is(':checked') ) {
			$( '.form-table-panel-ajax .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-ajax .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	// Displays AJAX enable confirmation alert.
	function enable_ajax_alert() {
        window.setTimeout(function () {
			var r = confirm("A page reload is required for this change.");
			if ( r == true ) {
				toggle_enable_ajax();
				$('#is-admin-form-element').submit();
			} else {
				if( $( '#_is_ajax-enable_ajax' ).is(':checked') ) {
					$('#_is_ajax-enable_ajax').prop('checked', false);
				} else {
					$('#_is_ajax-enable_ajax').prop('checked', true);
				}
			}
        }, 300);
	}

	$( '#_is_ajax-enable_ajax' ).on( 'click', function() {
		enable_ajax_alert();
	} );

	$( '.form-table-panel-ajax .is-field-disabled-message .message' ).on( 'click', function() {
		$( '#_is_ajax-enable_ajax' ).trigger('click');

	} );

	function toggle_analytics_info(){
		if ( '1' === $( '#is_disable_analytics' ).val() ) {
			$( '#is_disable_analytics' ).closest( 'div' ).find( '.analytics-info' ).hide();
		} else {
			$( '#is_disable_analytics' ).closest( 'div' ).find( '.analytics-info' ).show();
		}
	}
	toggle_analytics_info();

	$('#is_disable_analytics').on('click', function() {
		toggle_analytics_info();
	} );
} )( jQuery );
