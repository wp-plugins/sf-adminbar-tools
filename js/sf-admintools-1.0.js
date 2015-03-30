if (!(window.console && console.log)) {
	(function() {
		var noop = function() {};
		var methods = ['assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'markTimeline', 'table', 'time', 'timeEnd', 'timeStamp', 'trace', 'warn'];
		var length = methods.length;
		var console = window.console = {};
		while (length--) {
			console[methods[length]] = noop;
		}
	}());
}
// Same as hoverIntent (the default function for the admin bar) but with delegation
(function($) {
	$.fn.hoverIntentOn = function(f,g) {
		// default configuration options
		var cfg = {
			sensitivity: 7,
			interval: 100,
			timeout: 0,
			delegate: null
		};
		// override configuration options with user supplied object
		cfg = $.extend(cfg, g ? { over: f, out: g } : f );

		// instantiate variables
		// cX, cY = current X and Y position of mouse, updated by mousemove event
		// pX, pY = previous X and Y position of mouse, set by mouseover and polling interval
		var cX, cY, pX, pY;

		// A private function for getting mouse position
		var track = function(ev) {
			cX = ev.pageX;
			cY = ev.pageY;
		};

		// A private function for comparing current and previous mouse position
		var compare = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			// compare mouse positions to see if they've crossed the threshold
			if ( ( Math.abs(pX-cX) + Math.abs(pY-cY) ) < cfg.sensitivity ) {
				$(ob).off("mousemove", cfg.delegate, track);
				// set hoverIntent state to true (so mouseOut can be called)
				ob.hoverIntent_s = 1;
				return cfg.over.apply(ob,[ev]);
			} else {
				// set previous coordinates for next time
				pX = cX; pY = cY;
				// use self-calling timeout, guarantees intervals are spaced out properly (avoids JavaScript timer bugs)
				ob.hoverIntent_t = setTimeout( function(){compare(ev, ob);} , cfg.interval );
			}
		};

		// A private function for delaying the mouseOut function
		var delay = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			ob.hoverIntent_s = 0;
			return cfg.out.apply(ob,[ev]);
		};

		// A private function for handling mouse 'hovering'
		var handleHover = function(e) {
			// copy objects to be passed into t (required for event object to be passed in IE)
			var ev = jQuery.extend({},e);
			var ob = this;

			// cancel hoverIntent timer if it exists
			if (ob.hoverIntent_t) { ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t); }

			// if e.type == "mouseenter"
			if (e.type == "mouseenter") {
				// set "previous" X and Y position based on initial entry point
				pX = ev.pageX; pY = ev.pageY;
				// update "current" X and Y position based on mousemove
				$(ob).on("mousemove", track);
				// start polling interval (self-calling timeout) to compare mouse coordinates over time
				if (ob.hoverIntent_s != 1) { ob.hoverIntent_t = setTimeout( function(){compare(ev,ob);} , cfg.interval );}

			// else e.type == "mouseleave"
			} else {
				// unbind expensive mousemove event
				$(ob).off("mousemove", track);
				// if hoverIntent state is true, then call the mouseOut function after the specified delay
				if (ob.hoverIntent_s == 1) { ob.hoverIntent_t = setTimeout( function(){delay(ev,ob);} , cfg.timeout );}
			}
		};

		// bind the function to the two event listeners
		return this.on('mouseenter',cfg.delegate,handleHover).on('mouseleave',cfg.delegate,handleHover);
	};
})(jQuery);

// !Insert ajax loader after target
(function($){$.fn.abtLoader=function(){
	return this.after('<span class="loader">&#160;</span>');
	/*return this.after('<img class="loader" alt="" src="'+sfabt.imgs_url+'/ajax-loader.gif"/>');*/
};})(jQuery);

// !FadeIn and remove the target
(function($){$.fn.fadeInRemove=function(){
	return this.fadeIn(function(){ $(this).remove(); });
};})(jQuery);

// !slideUp and remove the target
(function($){$.fn.slideUpRemove=function(){
	return this.animate({'height': 0}, 300, function(){$(this).remove();});
};})(jQuery);

// !Remove the ajax loader placed after the target
(function($){$.fn.abtRemoveLoader=function(){
	return this.blur().next('.loader').fadeInRemove();
};})(jQuery);

// !Add a clone of a file in my list with its avatar
// @var $clr_btn: jQuery object of the "clear" button, the item will be inserted before it
// @var avatar: string, image to insert
(function($){$.fn.abtAddClone=function($clr_btn, avatar){
	var $clone = this.addClass('used-by-me').clone(true).height(0).attr('id', function(i,val){return val+'-own';});
	$clone.children('.loader').remove();
	$clone.insertBefore($clr_btn).animate({'height': '26px'}, 300);
	if( typeof avatar != 'undefined' && avatar !== '' )
		$clone.children('.ab-item').prepend(avatar);
	return this;
};})(jQuery);

// !Document ready
jQuery(document).ready(function($){

	var abt = {

		$body:		$("body"),
		$bar:		$(document.getElementById('wpadminbar')),
		$tools:		$(document.getElementById('wp-admin-bar-sf-abt-tools')),

		// !Files management
		$files_dd:	$(document.getElementById('wp-admin-bar-sf-abt-files')),
		$clr_btn:	$(document.getElementById('wp-admin-bar-sf-abt-close-files')),
		open_files:	'',			// Will store the open files. Will be used as a cache
		refc:		'',			// Will be used to refresh the counters every 5 minutes
		forceload:	'&force=1',	// Will be used to force ajax refresh when enabling/disabling coworking and for the first load of the page
		events:		0,			// Cowork events state

		// Messages in console
		log: function(code) {
			var msgs = {
				noConnexion:	'Error: can\'t establish a connexion :/',
				unkownError:	'The server told me there\'s an error somewhere :s',
				badResponse:	'Error: bad response from the server',
				noLocalStorage:	'Your browser does not support localStorage. The small adminbar and the floated admin menu will not be saved between pages.'
			};
			if ( typeof msgs.code == 'string' )
				console.log( msgs[code] );
			else
				console.log( code );
		},


		// !Hover the admin bar items: kill the original mouse events and use delegation for the "files tree"
		hoverIntent: function() {
			if(typeof(jQuery.fn.hoverIntent)!="undefined")
				abt.$files_dd.find("li.menupop").off('mouseover mouseout');

			abt.$bar.hoverIntentOn({
				over:		function(c){$(this).addClass("hover");},
				out:		function(c){$(this).removeClass("hover");},
				delegate:	"#wp-admin-bar-sf-abt-files li.menupop",
				timeout:	180,
				sensitivity:7,
				interval:	100
			});
		},

		// !Refresh the counters via ajax
		refresh_counters: function() {
			$.ajax({
				type:		'post',
				url:		sfabt.ajax_url+'?action=sf_abt_refresh_counters&nonce='+sfabt.refresh_counters_nonce,
				dataType:	"json"
			}).done(function(data){
				if (data && typeof data == 'object') {
					abt.update_counters(data.me, data.other);
					sfabt.cowork = parseInt(data.cowork,10) ? 'start' : 'stop';
					if ( sfabt.cowork && !abt.events )
						abt.cowork_events();
				}
			}).fail(function(){
				abt.log('noConnexion');
			});
		},

		// Function to update counters (top first)
		update_counters: function(me, other) {
			var $my_counters	= abt.$tools.find('.used-by-me-counter');
			var $other_counters	= abt.$tools.find('.used-by-other-counter');										/*abt.log(me);abt.log($my_counters);abt.log($my_counters.length);*/

			if ( !me )
				$my_counters.remove();
			else {
				if ( !$my_counters.length ) {
					$('<span id="used-by-me-small" class="hidden-when-not-small used-by-me-counter">'+me+'</span>').insertAfter('#wp-admin-bar-sf-abt-close-files .sf-abt-stats');
					$('<span id="used-by-me" class="used-by-me-counter">'+me+'</span>').insertAfter('#wp-admin-bar-sf-abt-main > .ab-item .sf-abt-stats');
				} else
					$my_counters.text(me);
			}

			if ( !other )
				$other_counters.remove();
			else {
				if ( !$other_counters.length ) {
					$('<span id="used-by-other-small" class="hidden-when-not-small used-by-other-counter">'+other+'</span>').appendTo('#wp-admin-bar-sf-abt-close-files > .ab-item');
					$('<span id="used-by-other" class="used-by-other-counter">'+other+'</span>').appendTo('#wp-admin-bar-sf-abt-main > .ab-item > .hidden-when-small');
				} else
					$other_counters.text(other);
			}
		},

		// !Function to update the top counters, depending on the ones at the bottom
		update_top_counters: function() {
			var $counter_me			= $(document.getElementById('used-by-me'));
			var $counter_other		= $(document.getElementById('used-by-other'));
			var $counter_me_small	= $(document.getElementById('used-by-me-small'));
			var $counter_other_small= $(document.getElementById('used-by-other-small'));

			if ( !$counter_me_small.length )
				$counter_me.remove();
			else {
				if ( !$counter_me.length )
					$counter_me		= $('<span id="used-by-me" class="used-by-me-counter">0</span>').insertAfter('#wp-admin-bar-sf-abt-main > .ab-item .sf-abt-stats');
				$counter_me.text($counter_me_small.text());
			}

			if ( !$counter_other_small.length )
				$counter_other.remove();
			else {
				if ( !$counter_other.length )
					$counter_other	= $('<span id="used-by-other" class="used-by-other-counter">0</span>').appendTo('#wp-admin-bar-sf-abt-main > .ab-item > .hidden-when-small');
				$counter_other.text($counter_other_small.text());
			}
		},

		// !Function to update the #used-by-me counters. i is the increment/decrement value
		increment_my_counters_by: function(i) {
			var $counter		= $(document.getElementById('used-by-me'));
			var $counter_small	= $(document.getElementById('used-by-me-small'));

			if ( !$counter.length )
				$counter		= $('<span id="used-by-me" class="used-by-me-counter">0</span>').insertAfter('#wp-admin-bar-sf-abt-main > .ab-item .sf-abt-stats');
			if ( !$counter_small.length )
				$counter_small	= $('<span id="used-by-me-small" class="hidden-when-not-small used-by-me-counter">0</span>').insertAfter('#wp-admin-bar-sf-abt-close-files .sf-abt-stats');

			var counterVal		= parseInt($counter.text(), 10) + parseInt(''+i, 10);
			if ( !counterVal ) {
				$counter.remove();
				$counter_small.remove();
			} else {
				$counter.text(counterVal);
				$counter_small.text(counterVal);
			}
		},

		// !Refresh the cowork files tree
		refresh_cowork_tree: function() {

			$.ajax({

				type:		'post',
				url:		sfabt.ajax_url+'?action=sf_abt_refresh_files&nonce='+sfabt.refresh_files_nonce+abt.forceload+'&cowork='+sfabt.cowork,
				data:		{files: abt.open_files},
				dataType:	"json",
				beforeSend:	function(){ $(document.getElementById('wp-admin-bar-sf-abt-coworking')).find('.ab-item').abtLoader(); }

			}).done(function(data){

				if (typeof data == 'object' && data !== null && typeof data.files != 'undefined') {

					if ( data.files == 'stop' ) {														// Cowork has been stoped from another page
						abt.forceload = '&force=1';		// Next refresh must be forced
						$(document.getElementById('wp-admin-bar-sf-abt-coworking')).children().text(sfabt.cowork_on).parent().siblings().slideUpRemove();	// Remove siblings and update text
						abt.update_counters(0, 0);		// Remove counters
						sfabt.cowork = 'stop';			// Cowork state
						if ( abt.events )
							abt.cowork_events();		// Stop events attachments
																														abt.log('cowork stoped');
					} else if ( data.files == 'not modified' ) {										// We sent the open_files json object, it's the same as the array saved in the database: we do nothing
																														abt.log('not modified');
					} else if ( typeof data.html != 'undefined' ) {														abt.log('total refresh');
						abt.$files_dd.replaceWith(data.html);
						abt.open_files	= data.files;
						// Reconstruct some vars
						abt.$files_dd	= abt.$tools.find('#wp-admin-bar-sf-abt-files').addClass('ab-submenu').removeClass('ab-top-menu');
						abt.$clr_btn	= abt.$files_dd.find('#wp-admin-bar-sf-abt-close-files');
						// Update counters
						abt.update_top_counters();
						// No need to force load now
						abt.forceload = '';
						// Update cowork value and start cowork events
						sfabt.cowork = 'start';
						if ( !abt.events )
							abt.cowork_events();
					} else {																							abt.log('partial refresh');
						var $dirs	= abt.$files_dd.children('.sf-abt-root-directory');					// Main folders (theme, plugins, muplugins)
						var $mylist	= abt.$files_dd.children('.used-by-me').find('.ab-item');			// My files (clones)
						var me_c	= 0;																// Count my files
						var other_c	= 0;

						for ( var file in data.files ) {
							if ( data.files[file] == sfabt.mid )
								me_c++;
							else
								other_c++;
							if ( typeof abt.open_files[file] != 'undefined' && data.files[file] == abt.open_files[file] ) {		// If we have the same entries both sides, we continue
								delete(abt.open_files[file]);																															abt.log(file+' not modified');
								continue;
							}
							var file_title = file.split(sfabt.content_dir).join('');

							if ( data.files[file] == sfabt.mid ) {										// Should it be mine?

								var $my_file = $dirs.find('[title="'+file_title+'"]').parent();
								// May be someone else was using it
								$my_file.find('.avatar').remove();
								$my_file.removeClass('used-by-other');
								// Add the "used-by-me" class, insert the clone in my list, add the avatar
								var avatar = typeof data.avatars != 'undefined' && typeof data.avatars[data.files[file]] != 'undefined' ? data.avatars[data.files[file]] : '';
								$my_file.abtAddClone(abt.$clr_btn, avatar);																												abt.log(file_title+' is mine now');

							} else {																	// It should be to someone else

								var $other_file = $dirs.find('[title="'+file_title+'"]').parent().removeClass('used-by-me');
								// Remove the clone in my list if it was mine
								$mylist.filter('[title="'+file_title+'"]').parent().slideUpRemove();
								// Add the "used-by-other" class, add the avatar
								$other_file.addClass('used-by-other');
								if( typeof data.avatars != 'undefined' && typeof data.avatars[data.files[file]] != 'undefined' && data.avatars[data.files[file]] !== '' && !$other_file.children('.ab-item').children('.avatar').length ) {
									$other_file.children('.ab-item').prepend(data.avatars[data.files[file]]);
								}																																						abt.log(file_title+' is not mine anymore');

							}

						}

						// Free up remaining files
						for ( var oldFile in abt.open_files ) {
							var oldFile_title = oldFile.split(sfabt.content_dir).join('');
							var $oldFile = $dirs.find('[title="'+oldFile_title+'"]').parent();
							$oldFile.find('.avatar').remove();
							$oldFile.removeClass('used-by-me used-by-other');
							$mylist.filter('[title="'+oldFile_title+'"]').parent().slideUpRemove();																						abt.log(oldFile_title+' removed');
						}
						// Now, update the numbers
						abt.update_counters(me_c, other_c);
						abt.open_files = data.files;
					}

				}

			}).fail(function(){
				abt.log('noConnexion');
			}).always(function(){											// Remove the loader img
				$(document.getElementById('wp-admin-bar-sf-abt-coworking')).find('.loader').fadeInRemove();
			});

		},		// eo refresh_cowork_tree

		// !All events for the cowork menus
		cowork_events: function() {
			if (sfabt.cowork == 'start') {

				// !Click on a file
				abt.$tools.on('click', '#wp-admin-bar-sf-abt-files .abt-file .ab-item', function(e){
					var $this	= $(this);
					var filepath= $this.attr('title');

					$.ajax({

						type:		'post',
						url:		sfabt.ajax_url+'?action=sf_abt_update_open_files&nonce='+sfabt.update_files_none,
						data:		{"file":filepath},
						dataType:	"json",
						beforeSend:	function(){ $this.abtLoader(); }

					}).done(function(data){

						if(typeof data.msg == 'string') {
							if ( typeof data.files != 'undefined' )
								abt.open_files = data.files;
							if(data.msg == 'you-got-it-my-young-padawan') {			// The file is all yours now
								var avatar = typeof data.avatar == 'string' ? data.avatar : '';
								$this.parent().abtAddClone(abt.$clr_btn, avatar);
								abt.increment_my_counters_by(1);
							} else if ( data.msg == 'i-m-not-a-number' ) {			// The file has been freed
								var path = $this.attr('title');
								abt.$files_dd.find('.sf-abt-root-directory .used-by-me .ab-item').filter('[title="'+path+'"]').parent().removeClass('used-by-me');
								$this.parent().slideUpRemove();
								abt.increment_my_counters_by(-1);
							} else if ( data.msg == 'you-shall-not-pass' ) {		// The file is already taken
								if ( !$this.parent().hasClass('used-by-other') ) {
									$this.parent().addClass('used-by-other');
									if(typeof data.avatar == 'string') {
										if(data.avatar !== '')
											$this.prepend(data.avatar);
									}
								}
							} else {
								abt.log('unknownError');
							}
						} else
							abt.log('badResponse');

					}).fail(function(){
						abt.log('noConnexion');
					}).always(function(){
						$this.abtRemoveLoader();
					});
					e.preventDefault();

				// !Close all my files
				}).on('click', '#wp-admin-bar-sf-abt-close-files .ab-item', function(e) {

					var $this = $(this);
					$.ajax({
						type:		'post',
						url:		sfabt.ajax_url+'?action=sf_abt_close_all_my_files&nonce='+sfabt.close_files_nonce,
						beforeSend:	function(){ $this.abtLoader(); }

					}).done(function(data){

						if (data == 'removed') {
							abt.$files_dd.find('.used-by-me').removeClass('used-by-me');
							$this.parent().siblings('.abt-file').slideUpRemove();
							$('#used-by-me, #used-by-me-small').remove();
						} else
							abt.log('unkownError');

					}).fail(function(){
						abt.log('noConnexion');
					}).always(function(){
						$this.abtRemoveLoader();
					});
					e.preventDefault();

				// !Refresh files tree
				}).on('mouseenter', '#wp-admin-bar-sf-abt-main > .ab-item', function(e) {

					abt.refresh_cowork_tree();
					e.preventDefault();

				// !Deal with menu height and window size
				}).on('mouseenter', '.menupop', function(e){

					$(this).each(function(){
						var $this	= $(this);
						var $window	= $(window);
						var $wrap	= $this.children('.ab-sub-wrapper');
						var offtop	= $this.offset().top - $window.scrollTop();
						var wraph	= $wrap.outerHeight();
						var wrapb	= $wrap.css('bottom');
						if ( (offtop != 73) && ( offtop - wraph < 0 ) && ( offtop + wraph > $window.height() ) ) {
							$wrap.css({'top': function(){ return 73-offtop; }, 'bottom': 'auto'});
						} else if ( ( wrapb != '7px' ) && ( offtop - wraph > 0 ) && ( offtop + wraph > $window.height() ) ) {
							$wrap.css({'bottom': -7, 'top': 'auto'});
						} else if ( wrapb != 'auto' ) {
							$wrap.css({'bottom': 'auto', 'top': 'auto'});
						}
					});

				});

				if ( sfabt.refresh_cowork == '1' )
					abt.refc = setInterval(abt.refresh_counters, 300000); // !Refresh the counters every 5 minutes
				abt.events = 1;

			} else if (sfabt.cowork == 'stop') {
				abt.$tools.off('click mouseenter', '**');
				if ( sfabt.refresh_cowork == '1' )
					abt.refc = clearInterval(abt.refc);
				abt.events = 0;
			}
		},		// eo cowork_events

		// !Init
		init: function() {

			if(window.localStorage) {
				// !Initial states: small admin bar and fixed admin menu
				if ( localStorage.getItem('smallAdminBar') )
					abt.$body.addClass("admin-bar-small");
				if ( abt.$body.hasClass('wp-admin') && localStorage.getItem('fixedAdminMenu') )
					abt.$body.addClass("admin-menu-fixed");

				// !Final states: only on window unload
				window.onunload = function() {
					if ( abt.$body.hasClass("admin-bar-small") ) {
						localStorage.setItem('smallAdminBar', 1);
					} else {
						localStorage.removeItem('smallAdminBar');
					}
					if ( abt.$body.hasClass('admin-menu-fixed') ) {
						localStorage.setItem('fixedAdminMenu', 1);
					} else if ( abt.$body.hasClass('wp-admin') ) {
						localStorage.removeItem('fixedAdminMenu');
					}
				};
			} else {
				abt.log('noLocalStorage');
			}

			// !Retract admin bar
			abt.$bar.on('click', '#wp-admin-bar-sf-abt-main>.ab-item', function(e){
				abt.$body.toggleClass('admin-bar-small');
				e.preventDefault();
			});

			// !Float the admin menu
			if( abt.$body.hasClass('wp-admin') ) {
				abt.$bar.on('click', '#wp-admin-bar-sf-abt-menuWpQuery > .ab-item', function(e){
					abt.$body.toggleClass('admin-menu-fixed');
					e.preventDefault();
				});
			} else {
				// !Call $wp_query with ajax
				abt.$body.on('click', '#wp-admin-bar-sf-abt-menuWpQuery > .ab-item, #sf-abt-h2', function(e){
					var $preWrap = $(document.getElementById('sf-abt-pre-wrap'));
					if ( $preWrap.length && $preWrap.hasClass('is-404') ) {		// In 404 pages, the lightbox is allready printed
						$preWrap.show();
					} else {
						var $preCont = $(document.getElementById('sf-abt-pre-cont'));
						if ( !$preCont.length )
							$preCont = $('<div id="sf-abt-pre-cont"/>').appendTo(abt.$body);
						var sep = '?';
						var pageUrl = window.location.href;
						if ( pageUrl.indexOf('#')!=-1 )	pageUrl = pageUrl.split('#')[0];
						if ( pageUrl.indexOf('?')!=-1 )	sep = '&';
						$preCont.html('').load(pageUrl+sep+'_wpnonce='+sfabt.query_nonce+'&wp_query&noheader #sf-abt-pre-wrap');
					}
					e.preventDefault();
				}).on('click', '#sf-abt-pre-wrap', function(e){		// Close the $wp_query lightbox
					if (e.target===this) {
						var $this = $(this);
						if ( $this.hasClass('is-404') )
							$this.hide();
						else
							$this.parent().remove();
						e.preventDefault();
					}
				});
			}

			// !Action input fields (admin)
			abt.$tools.on('click, focus', 'input.no-adminbar-style', function(e){
				var $this		= $(this);
				var oldValue	= this.defaultValue;
				var nbr_params	= $this.data('nbrparams');
				var newValue	= "add_action( '" + oldValue + "', ''" + (nbr_params != 'undefined' && nbr_params > 1 ? ", 10, " + nbr_params : "") + " );";
				var initwidth	= $this.width();
				$this.val(newValue).data('initwidth', initwidth).css('width', function(){ return $this.parent().width(); });
				this.select();
			}).on('blur', 'input.no-adminbar-style', function(e){
				var $this		= $(this);
				var newValue	= this.defaultValue;
				$this.val(newValue).css('width', function(){ return $this.data('initwidth'); }).removeData('initwidth');
			});

			// !COWORKING

			// !Maybe refresh tree when we get back to the window. If we need to refresh the whole tree, we only refresh counters to not freeze the screen in case of large tree
			if ( sfabt.refresh_cowork == '1' )
				$(window).on('focus', function(e) {
					if ( abt.forceload )
						abt.refresh_counters();
					else
						abt.refresh_cowork_tree();
				});

			// !Enable/Disable coworking
			abt.$bar.on('click', '#wp-admin-bar-sf-abt-coworking .ab-item', function(e) {
				var $this = $(this);
				$.ajax({
					type: 'post',
					url: sfabt.ajax_url+'?action=sf_abt_enable_coworking&nonce='+sfabt.coworking_nonce,
					beforeSend: function(){
						$this.abtLoader();
					}
				}).done(function(data){
					if (data == 'start') {																		abt.log('Starting cowork');
						sfabt.cowork = 'start';
						if ( !abt.events )
							abt.cowork_events();																// Start events attachments
						abt.refresh_cowork_tree();																// Refresh the files tree
					} else {																					abt.log('Stoping cowork');
						abt.forceload = '&force=1';
						$this.text(sfabt.cowork_on).parent().siblings().slideUpRemove();						// Remove all siblings and update text
						abt.update_counters(0, 0);																// Remove counters
						sfabt.cowork = 'stop';
						if ( abt.events )
							abt.cowork_events();																// Stop events attachments
					}
				}).fail(function(){
					abt.log('noConnexion');
				}).always(function(){
					$this.abtRemoveLoader();		// Remove the loader img
				});
				e.preventDefault();
			});

			// !Menu hover delegation
			abt.hoverIntent();

			// !Launch cowork events
			if ( !abt.events )
				abt.cowork_events();

		}	// eo init


	};

	// !Start all this mess
	abt.init();

});