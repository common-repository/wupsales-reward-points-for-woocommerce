"use strict";
var wsbpAdminFormChanged = [];
window.onbeforeunload = function(){
	// If there are at lease one unsaved form - show message for confirnation for page leave
	if(wsbpAdminFormChanged.length)
		return 'Some changes were not-saved. Are you sure you want to leave?';
};
jQuery(document).ready(function(){
	if(typeof(wsbpActiveTab) != 'undefined' && wsbpActiveTab != 'main_page' && jQuery('#toplevel_page_wsbp-comparison-slider').hasClass('wp-has-current-submenu')) {
		var subMenus = jQuery('#toplevel_page_wsbp-comparison-slider').find('.wp-submenu li');
		subMenus.removeClass('current').each(function(){
			if(jQuery(this).find('a[href$="&tab='+ wsbpActiveTab+ '"]').size()) {
				jQuery(this).addClass('current');
			}
		});
	}

	wsbpInitSettingsParents();
		
	wsbpInitStickyItem();

	jQuery('.navigation-bar').on('click', function() {
		var navMenu = jQuery('.wupsales-navigation');

		if (navMenu.hasClass('wupsales-navigation-show')) navMenu.removeClass('wupsales-navigation-show');
		else navMenu.addClass('wupsales-navigation-show');		
	});
	
	wsbpInitTooltips();
	jQuery(document.body).on('changeTooltips', function (e) {
		wsbpInitTooltips(e.target);
	});
	wsbpInitColorPicker();
	jQuery('.wupsales-panel').on('click focus', '.wupsales-shortcode', function(e) {
		e.preventDefault();
		this.setSelectionRange(0, this.value.length);
	});
	jQuery('.wupsales-namefile').disableSelection();
	jQuery('.wupsales-inputfile input').on('change', function(e) {
		e.preventDefault();
		jQuery(this).parent('.wupsales-inputfile').find('.wupsales-namefile').html(this.files.length ? this.files[0].name : '');
	});

	jQuery('.wupsales-plugin-loader').css('display', 'none');
	jQuery('.wupsales-main').css('display', 'block');
	//wsbpInitMultySelects();
	
	jQuery('.wupsales-plugin .tooltipstered').removeAttr("title");
});
function wsbpInitSettingsParents( selector ) {
	var settingsValues = selector ? selector : jQuery('.wupsales-panel');

	settingsValues.on('change wsbp-change', 'input[type="checkbox"]', function () {
		var elem = jQuery(this),
			valueWrapper = elem.closest('.options-value'),
			name = elem.attr('name'),
			block = settingsValues,
			childrens = block.find('.row-options-block[data-parent="' + name + '"], .options-value[data-parent="' + name + '"]');
		if(childrens.length > 0) {
			if(elem.is(':checked') && (valueWrapper.length == 0 || !valueWrapper.hasClass('wupsales-hidden'))) {
				childrens.removeClass('wupsales-hidden');
				childrens.find('select,input[type="checkbox"]').trigger('wsbp-change');
			} else childrens.addClass('wupsales-hidden');
		}
	});
	settingsValues.on('change wsbp-change', 'select', function () {
		var elem = jQuery(this),
			value = elem.val(),
			hidden = elem.closest('.options-value').hasClass('wupsales-hidden'),
			name = elem.attr('name'),
			block = settingsValues,
			subOptions = block.find('.row-options-block[data-select="' + name + '"], .options-value[data-select="' + name + '"]');
		if(subOptions.length) {
			subOptions.addClass('wupsales-hidden');
			if(!hidden) subOptions.filter('[data-select-value*="'+value+'"]').removeClass('wupsales-hidden');
		}
	});
}
function wsbpInitMultySelects( selector ) {
	var multySelects = jQuery(selector ? selector : '.wupsales-panel').find('select.wupsales-chosen:not(.no-chosen)');
	if (multySelects.length) {
		multySelects.chosen({width: "100%"});
		multySelects.on('change', function (e, info) {
			if (info.selected) {
				var allSelected = this.querySelectorAll('option[selected]'),
					lastSelected = allSelected[allSelected.length - 1],
					selected = this.querySelector(`option[value="${info.selected}"]`);
				selected.setAttribute('selected', '');
				if (lastSelected) lastSelected.insertAdjacentElement('afterEnd', selected);
				else this.insertAdjacentElement('afterbegin', selected);
			} else {
				var removed = this.querySelector(`option[value="${info.deselected}"]`);
				removed.setAttribute('selected', false); // this step is required for Edge
				removed.removeAttribute('selected');
			}
			jQuery(this).trigger('chosen:updated');
		});

	}
}
function wsbpInitToggleBlocks( selector ) {
	jQuery(selector).off('click', '.wsbp-toggle').on('click', '.wsbp-toggle', function(e){
		e.preventDefault();
		var el = jQuery(this),
			i = el.find('i'),
			options = el.closest('.wsbp-table-row').find('.wsbp-toggle-block');

		if (i.hasClass('fa-chevron-down')){
			i.removeClass('fa-chevron-down').addClass('fa-chevron-up');
			options.removeClass('wupsales-hidden');
		} else {
			i.removeClass('fa-chevron-up').addClass('fa-chevron-down');
			options.addClass('wupsales-hidden');
		}
	});
}
	
function wsbpInitTooltips( selector ) {
	var tooltipsterSettings = {
			contentAsHTML: true,
			interactive: true,
			speed: 0,
			delay: 200,
			maxWidth: 450
		},
		findPos = {
			'.wupsales-tooltip': 'top-left',
			'.wupsales-tooltip-bottom': 'bottom-left',
			'.wupsales-tooltip-left': 'left',
			'.wupsales-tooltip-right': 'right'
		},
		$findIn = selector ? jQuery( selector ) : false;
	for(var k in findPos) {
		if(typeof(k) === 'string') {
			var $tips = $findIn ? $findIn.find( k ) : jQuery( k ).not('.no-tooltip');
			if($tips && $tips.size()) {
				tooltipsterSettings.position = findPos[ k ];
				// Fallback for case if library was not loaded
				if(!$tips.tooltipster) continue;
				$tips.tooltipster( tooltipsterSettings );
			}
		}
	}
	if ($findIn) {
		$findIn.find('.tooltipstered').removeAttr('title');
	}
}
function wsbpInitColorPicker(selector) {
	var $findIn = selector ? jQuery(selector) : jQuery('.wupsales-plugin');
	$findIn.find('.wupsales-color-picker').each(function() {
		var $this = jQuery(this),
			colorArea = $this.find('.wupsales-color-preview'),
			colorInput = $this.find('.wupsales-color-input'),
			curColor = colorInput.val(),
			timeoutSet = false;

		colorArea.ColorPicker({
			flat: false,
			onShow: function (colpkr) {
				jQuery(this).ColorPickerSetColor(colorInput.val());
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				var self = this;
				curColor = hex;
				if(!timeoutSet) {
					setTimeout(function(){
						timeoutSet = false;
						jQuery(self).find('.colorpicker_submit').trigger('click');
					}, 500);
					timeoutSet = true;
				}
			},
			onSubmit: function(hsb, hex, rgb, el) {
				setColorPickerPreview(colorArea, '#' + curColor);
				colorInput.val('#' + curColor).trigger('change');					
			}
		});
		setColorPickerPreview(colorArea, colorInput.val());
	});
	$findIn.find('.wupsales-color-input').on('change', function() {
		setColorPickerPreview(jQuery(this).parent().find('.wupsales-color-preview'), jQuery(this).val());
	});
	function setColorPickerPreview(area, col) {
		area.css({'backgroundColor': col, 'border-color': wsbpGetColorPickerBorder(col)});
	}
}
function wsbpInitCheckAll(elem, preName) {
	if (typeof preName == 'undefined') var preName = 'wsbpCheck';
	var main = elem.find('.' + preName + 'All');
	if (main.length) {
		main.on('change', function(e) {
			e.preventDefault();
			elem.find('.' + preName + 'One').prop('checked', jQuery(this).is(':checked'));
		});
		elem.on('change', '.' + preName + 'One', function(e){
			e.preventDefault();
			if (!jQuery(this).is(':checked')) {
				main.prop('checked', false);
			}
		});
	}
}
function changeAdminFormWsbp(formId) {
	if(jQuery.inArray(formId, wsbpAdminFormChanged) == -1)
		wsbpAdminFormChanged.push(formId);
}
function adminFormSavedWsbp(formId) {
	if(wsbpAdminFormChanged.length) {
		for(var i in wsbpAdminFormChanged) {
			if(wsbpAdminFormChanged[i] == formId) {
				wsbpAdminFormChanged.pop(i);
			}
		}
	}
}
function checkAdminFormSaved() {
	if(wsbpAdminFormChanged.length) {
		if(!confirm('Some changes were not-saved. Are you sure you want to leave?')) {
			return false;
		}
		wsbpAdminFormChanged = [];	// Clear unsaved forms array - if user wanted to do this
	}
	return true;
}
function isAdminFormChanged(formId) {
	if(wsbpAdminFormChanged.length) {
		for(var i in wsbpAdminFormChanged) {
			if(wsbpAdminFormChanged[i] == formId) {
				return true;
			}
		}
	}
	return false;
}
/*Some items should be always on users screen*/
function wsbpInitStickyItem() {
	jQuery(window).scroll(function(){
		var stickiItemsSelectors = ['.wupsales-sticky']
		,	elementsUsePaddingNext = ['.wupsales-bar']	// For example - if we stick row - then all other should not offest to top after we will place element as fixed
		,	wpTollbarHeight = 32
		,	wndScrollTop = jQuery(window).scrollTop() + wpTollbarHeight
		,	footer = jQuery('.wsbpAdminFooterShell')
		,	footerHeight = footer && footer.size() ? footer.height() : 0
		,	docHeight = jQuery(document).height()
		,	wasSticking = false
		,	wasUnSticking = false;
		for(var i = 0; i < stickiItemsSelectors.length; i++) {
			jQuery(stickiItemsSelectors[ i ]).each(function(){
				var element = jQuery(this);
				if(element && element.size() && !element.hasClass('sticky-ignore')) {
					var scrollMinPos = element.offset().top
					,	prevScrollMinPos = parseInt(element.data('scrollMinPos'))
					,	useNextElementPadding = toeInArrayWsbp(stickiItemsSelectors[ i ], elementsUsePaddingNext) || element.hasClass('sticky-padd-next')
					,	currentScrollTop = wndScrollTop
					,	calcPrevHeight = element.data('prev-height')
					,	currentBorderHeight = wpTollbarHeight
					,	usePrevHeight = 0;
					if(calcPrevHeight) {
						usePrevHeight = jQuery(calcPrevHeight).outerHeight();
						currentBorderHeight += usePrevHeight;
					}
					if(currentScrollTop > scrollMinPos && !element.hasClass('wupsales-sticky-active')) {	// Start sticking
						if(element.hasClass('sticky-save-width')) {
							element.width( element.width() );
						}
						element.addClass('wupsales-sticky-active').data('scrollMinPos', scrollMinPos).css({
							'top': currentBorderHeight
						});
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								nextElement.data('prevPaddingTop', nextElement.css('padding-top'));
								var addToNextPadding = parseInt(element.data('next-padding-add'));
								addToNextPadding = addToNextPadding ? addToNextPadding : 0;
								nextElement.css({
									'padding-top': (element.hasClass('sticky-outer-height') ? element.outerHeight() : element.height()) + usePrevHeight + addToNextPadding
								});
							}
						}
						wasSticking = true;
						element.trigger('startSticky');
					} else if(!isNaN(prevScrollMinPos) && currentScrollTop <= prevScrollMinPos) {	// Stop sticking
						element.removeClass('wupsales-sticky-active').data('scrollMinPos', 0).css({
							'top': 0
						});
						if(element.hasClass('sticky-save-width')) {
							if(element.hasClass('sticky-base-width-auto')) {
								element.css('width', 'auto');
							}
						}
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								var nextPrevPaddingTop = parseInt(nextElement.data('prevPaddingTop'));
								if(isNaN(nextPrevPaddingTop))
									nextPrevPaddingTop = 0;
								nextElement.css({
									'padding-top': nextPrevPaddingTop
								});
							}
						}
						element.trigger('stopSticky');
						wasUnSticking = true;
					} else {	// Check new stick position
						if(element.hasClass('wupsales-sticky-active')) {
							if(footerHeight) {
								var elementHeight = element.height()
								,	heightCorrection = 32
								,	topDiff = docHeight - footerHeight - (currentScrollTop + elementHeight + heightCorrection);
								if(topDiff < 0) {
									element.css({
										'top': currentBorderHeight + topDiff
									});
								} else {
									element.css({
										'top': currentBorderHeight
									});
								}
							}
							// If at least on element is still sticking - count it as all is working
							wasSticking = wasUnSticking = false;
						}
					}
				}
			});
		}
	});
}
function wsbpGetTxtEditorVal(id) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible') 
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		return tinyMCE.get( id ).getContent();
	else
		return jQuery('#'+ id).val();
}
function wsbpSetTxtEditorVal(id, content) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible')
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		tinyMCE.get( id ).setContent(content);
	else
		jQuery('#'+ id).val( content );
}

function prepareToPlotDate(data) {
	if(typeof(data) === 'string') {
		if(data) {
			data = wsbpStrReplace(data, '/', '-');
			return (new Date(data)).getTime();
		}
	}
	return data;
}
function wsbpInitPlugNotices() {
	var $notices = jQuery('.wupsales-admin-notice');
	if($notices && $notices.size()) {
		$notices.each(function(){
			jQuery(this).find('.notice-dismiss').click(function(){
				var $notice = jQuery(this).parents('.wupsales-admin-notice');
				if(!$notice.data('stats-sent')) {
					// User closed this message - that is his choise, let's respect this and save it's saved status
					jQuery.sendFormWsbp({
						data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: 'hide'}
					});
				}
			});
			jQuery(this).find('[data-statistic-code]').click(function(){
				var href = jQuery(this).attr('href')
				,	$notice = jQuery(this).parents('.wupsales-admin-notice');
				jQuery.sendFormWsbp({
					data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: jQuery(this).data('statistic-code')}
				});
				$notice.data('stats-sent', 1).find('.notice-dismiss').trigger('click');
				if(!href || href === '' || href === '#')
					return false;
			});
		});
	}
}
