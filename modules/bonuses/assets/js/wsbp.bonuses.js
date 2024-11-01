(function ($, app) {
"use strict";
	function CartBonuses() {
		this.$obj = this;
		return this.$obj;
	}
	
	CartBonuses.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = WSBP_DATA.isPro == '1';
		
		_this.eventsCartBonuses();
		if (typeof(_this.initPro) == 'function') _this.initPro();
	}
	CartBonuses.prototype.eventsCartBonuses = function () {
		var _this = this.$obj;
		
		$('body').on('change', '.wsbp-cart-enabled input', function() {
			if ($(this).is(':checked')) $('.wsbp-cart-form').removeClass('wsbp-hidden');
			else $('.wsbp-cart-form').addClass('wsbp-hidden');
		});
		$('.wsbp-cart-enabled input').trigger('change');
		
		$('body').on('click', '.wsbp-cart-add-discount', function() {
			var $btn = $(this),
				$form = $btn.closest('.wsbp-cart-form'),
				cnt = $form.find('input[name="wsbp_points"]').val();
			$.sendFormWsbp({
				btn: $btn,
				data: {
					mod: 'bonuses', 
					action: 'addUserCartPointsDiscount', 
					wsbpNonce: $form.find('input[name="wsbp_nonce"]').val(),
					points: cnt.length ? cnt : 0,
					rate: $form.find('input[name="wsbp_rate"]').val()
				},
				onSuccess: function(res) {
					if (res.data) {
						$form.find('input[name="wsbp_points"]').val(res.data.points);
					}
					if (!res.error) {
						if ($('.wc-block-components-totals-coupon').length) {
							wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
						} else {
							$('[name="update_cart"]').removeAttr('disabled').trigger('click');
							$(document.body).trigger('update_checkout');
						}
					}
				}
			});
			return false;
		});
	}
	
	function WidgetBonuses() {
		this.$obj = this;
		return this.$obj;
	}
	
	WidgetBonuses.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = WSBP_DATA.isPro == '1';
		_this.dateFormat = WSBP_DATA.dateFormat;
		_this.widgetPopup = false;
		_this.showWaitPopup = true;
		
		_this.eventsWidgetBonuses();
		if (typeof(_this.initPro) == 'function') _this.initPro();
		
	}
	WidgetBonuses.prototype.eventsWidgetBonuses = function () {
		var _this = this.$obj;
		$('body').on('click wsbp-click', '.wsbp-widget-wrapper', function(e) {
			e.preventDefault();
			if ($(this).closest('.wsbp-popup-content').length) return;
			if (!_this.widgetPopup) {
				_this.widgetPopup = $('<div class="wsbp-widget-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content"><div class="wsbp-popup-loader"></div></div></div>');
				_this.widgetPopup.appendTo($('body'));
				$.sendFormWsbp({
					url: WSBP_DATA.ajaxurl+(WSBP_DATA.ajaxurl.indexOf('?') == -1 ? '?' : '&')+'currency='+$(e.currentTarget).attr('data-currency'),
					data: {
						mod: 'bonuses', 
						action: 'getWidgetPopup', 
						front: true,
					},
					onSuccess: function(res) {
						if (!res.error && res.data) {
							_this.widgetPopupLoaded(res.data);
						}
					}
				});
			}
			if (_this.showWaitPopup || e.type == 'click') {
				_this.widgetPopup.bPopup({
					closeClass:'wsbp-b-close',
					speed: 450,
					transition: 'slideDown'
					});
			}
			return false;
		});
		$('.wsbp-badge-wrapper, .wsbp-widget-wrapper:not(.wsbp-inline)').css('display', 'block'); 
		_this.showWidget = WSBP_FRONT.showWidget;
		if (_this.showWidget) {
			_this.showWaitPopup = false;
			$('.wsbp-widget-wrapper:first').trigger('wsbp-click');
		}
		
	}
	WidgetBonuses.prototype.widgetPopupLoaded = function (data) {
		var _this = this.$obj;
		if (!_this.widgetPopup || !data.html) return;
		if (data.css) $(data.css).appendTo('head');
		//if (data.css) data.html = data.css+data.html;
		
		_this.widgetPopup.find('.wsbp-popup-content').html(data.html);
		if (!_this.showWaitPopup) {
			_this.widgetPopup.bPopup({
				closeClass:'wsbp-b-close',
				speed: 450,
				transition: 'slideDown'
				});
			_this.showWaitPopup = true;
		}
		
		var $mainTabs = _this.widgetPopup.find('.wsbp-widget-tabs .wsbp-widget-tab'),
			$mainTabsContent = $('.wsbp-widget-tab-content > .wsbp-block-tab'),
			$curTab = $mainTabs.filter('.current');
		$mainTabsContent.filter($curTab.data('tab')).addClass('active');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.data('tab');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');
			$this.blur();

			var $curTabContent = $mainTabsContent.filter($curTab);
			$curTabContent.addClass('active');

		});
		_this.widgetPopup.on('click', '.wsbp-toggle', function(e){
			e.preventDefault();
			var el = $(this),
				i = el.find('i'),
				details = el.closest('.block-tab-balance').find('.wsbp-balance-detail');

			if (i.hasClass('fa-chevron-down')){
				i.removeClass('fa-chevron-down').addClass('fa-chevron-up');
				details.removeClass('wsbp-hidden');
			} else {
				i.removeClass('fa-chevron-up').addClass('fa-chevron-down');
				details.addClass('wsbp-hidden');
			}
		});
		_this.widgetPopup.find('.wsbp-field-date').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: _this.dateFormat,
			showAnim: '',
			yearRange: "-100:+0"
		});
		$('#ui-datepicker-div').addClass('wsbp-dp');
		var $filter = _this.widgetPopup.find('.wsbp-trans-filter'),
			$table = _this.widgetPopup.find('.wsbp-widget-trans');
		$table.find('th').on('click', function() {
			var $this = $(this),
				dir = $this.find('.wsbp-sort-asc').length ? 'desc' : 'asc';
			$this.closest('tr').find('.ui-icon').remove();
			$('<span class="ui-icon wsbp-sort-' + dir + '"></span>').prependTo($this);
			$filter.find('input[name="sort"]').val($this.data('field'));
			$filter.find('input[name="dir"]').val(dir);
			$filter.find('.wsbp-trans-show').trigger('click');
		});
		$filter.find('.wsbp-trans-show').on('click', function() {
			var $btn = $(this);
			$.sendFormWsbp({
				btn: $btn,
				data: {
					mod: 'bonuses', 
					action: 'getUserTransactions',
					userId: $filter.find('input[name="userId"]').val(),
					params: {
						from: $filter.find('input[name="from"]').val(),
						to: $filter.find('input[name="to"]').val(),
						sort: $filter.find('input[name="sort"]').val(),
						dir: $filter.find('input[name="dir"]').val(),
						front: $filter.find('input[name="front"]').val()
					}
				},
				onSuccess: function(res) {
					if (!res.error && res.html) {
						$table.find('tbody').html(res.html);
					}
				}
			});
		});
		var $settings = _this.widgetPopup.find('.block-tab-settings');
		$settings.find('.wsbp-birthday-save').on('click', function() {
			var $btn = $(this);
			$.sendFormWsbp({
				btn: $btn,
				data: {
					mod: 'actions', 
					action: 'saveUserBirthday',
					birthday: $settings.find('input[name="birthday"]').val(),
					wsbpNonce: WSBP_FRONT.wsbpNonce
				},
				onSuccess: function(res) {
					if (!res.error) {
						_this.showStatusButtons(res.user_status, res.is_age_pass);
					}
				}
			});
		});
		$settings.find('.wsbp-confirm, .wsbp-refuse').on('click', function() {
			var $btn = $(this);
			$.sendFormWsbp({
				btn: $btn,
				data: {
					mod: 'actions', 
					action: 'setUserStatus',
					status: $btn.hasClass('wsbp-confirm') ? 1 : 0,
					wsbpNonce: WSBP_FRONT.wsbpNonce
				},
				onSuccess: function(res) {
					if (!res.error) {
						_this.showStatusButtons(res.user_status, res.is_age_pass);
					}
				}
			});
		});
	}
	WidgetBonuses.prototype.showStatusButtons = function (status, isAgePass) {
		var _this = this.$obj,
			$settings = _this.widgetPopup.find('.block-tab-settings');
		$settings.find('.wsbp-user-status').addClass('wsbp-hidden');
		$settings.find('.wsbp-widget-buttons button').addClass('wsbp-hidden');
		if(typeof(status) != 'undefined') {
			status = parseInt(status);
			switch (status) {
				case 0:
					$settings.find('.wsbp-user-refused').removeClass('wsbp-hidden');
					$settings.find('.wsbp-confirm').removeClass('wsbp-hidden');
					break;
				case 1:
					$settings.find(parseInt(isAgePass) == 1 ? '.wsbp-user-active' : '.wsbp-user-agelimit').removeClass('wsbp-hidden');
					$settings.find('.wsbp-refuse').removeClass('wsbp-hidden');
					break;
				case 2:
					$settings.find('.wsbp-user-blocked').removeClass('wsbp-hidden');
					break;
				default:
					break;
			}
		}
	}
	
	function PopupBonuses() {
		this.$obj = this;
		return this.$obj;
	}
	
	PopupBonuses.prototype.init = function () {
		var _this = this.$obj;
		_this.startPopupTimer();
	}
	
	PopupBonuses.prototype.startPopupTimer = function() {
		var _this = this.$obj;
		_this.actionPopup = false;
		
		_this.getActionPopup = function() {
			clearInterval(_this.timerIntervalId);
			$.sendFormWsbp({
				data: {
					mod: 'actions', 
					action: 'getActionPopup', 
				},
				onSuccess: function(res) {
					if (!res.error && res.html && res.html.length) {
						_this.showPopup(res.html, true); 
					}
				}
			});
		}
		_this.timerIntervalId = setInterval(_this.getActionPopup, 5000);
	}
	PopupBonuses.prototype.showPopup = function($html, restart) {
		var _this = this.$obj;
		if (!_this.actionPopup) {
			_this.actionPopup = $('<div class="wsbp-widget-popup wsbp-info-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content"></div></div>');
			_this.actionPopup.appendTo($('body'));
		}
		_this.actionPopup.find('.wsbp-popup-content').html($html);
						
		_this.actionPopup.bPopup({
			closeClass: 'wsbp-b-close',
			speed: 450,
			transition: 'slideIn',
			onClose: function(){
				if (restart) _this.timerIntervalId = setInterval(_this.getActionPopup, 5000);
			}
		});
	}

	app.wsbpCartBonuses = new CartBonuses();
	app.wsbpWidgetBonuses = new WidgetBonuses();
	app.wsbpPopupBonuses = new PopupBonuses();

	$(document).ready(function () {
		app.wsbpCartBonuses.init();
		app.wsbpWidgetBonuses.init();
		app.wsbpPopupBonuses.init();
	});
}(window.jQuery, window));