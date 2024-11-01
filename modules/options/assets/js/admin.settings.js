(function ($, app) {
"use strict";
	function SettingsPage() {
		this.$obj = this;
		return this.$obj;
	}
	
	SettingsPage.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = WSBP_DATA.isPro == '1';
		_this.langSettings = wsbpParseJSON($('#wsbpLangSettingsJson').val());
		
		_this.eventsSettingsPage();
		if (typeof(_this.initPro) == 'function') _this.initPro();
	}
	
	SettingsPage.prototype.eventsSettingsPage = function () {
		var _this = this.$obj,
			$mainTabs = $('.wsbp-main-tabs .button'),
			$mainTabsContent = $('.wsbp-main-tab-content > .block-tab'),
			$curTab = $mainTabs.filter('.current');
		$mainTabsContent.filter($curTab.attr('href')).addClass('active');
		_this.curTabModel = $curTab.attr('data-model');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');
			$this.blur();

			var $curTabContent = $mainTabsContent.filter($curTab);
			$curTabContent.addClass('active');
		});
		
		$('#wsbpResetRulesPage').on('click', function() {
			if (confirm(wsbpCheckSettings(_this.langSettings, 'confirm-rules'))) {
				$.sendFormWsbp({
					btn: $('#wsbpResetRulesPage'),
					data: {
						mod: 'options', 
						action: 'resetRulesPage', 
					}
				});
			}
		});
		$('#wsbpStopBonusProgram').on('click', function() {
			$.sendFormWsbp({
				btn: $('#wsbpStopBonusProgram'),
				data: {
					mod: 'options', 
					action: 'activateBonusProgram', 
					activate: 0, 
				},
				onSuccess: function(res) {
					if (!res.error) {
						$('.wsbp-active-program').addClass('wupsales-hidden');
						$('.wsbp-active-program[data-active="0"]').removeClass('wupsales-hidden');
					}
				}
			});
		});
		$('#wsbpRunBonusProgram').on('click', function() {
			$.sendFormWsbp({
				btn: $('#wsbpRunBonusProgram'),
				data: {
					mod: 'options', 
					action: 'activateBonusProgram', 
					activate: 1, 
				},
				onSuccess: function(res) {
					if (!res.error) {
						$('.wsbp-active-program').addClass('wupsales-hidden');
						$('.wsbp-active-program[data-active="1"]').removeClass('wupsales-hidden');
					}
				}
			});
		});
	
		$('#wsbpBtnSave').click(function(){
			$('#wsbpSettingsForm').submit();
			return false;
		});
		$('#wsbpSettingsForm').submit(function(){
			_this.beforeSave();
			$(this).sendFormWsbp({
				btn: $('#wsbpBtnSave')
			});
			return false;
		});
		wsbpInitMultySelects('#block-tab-main');
	}
	
	SettingsPage.prototype.beforeSave = function () {
		var _this = this.$obj;
		if (typeof(_this.beforeSavePro) == 'function') _this.beforeSavePro();
	}
	
	app.wsbpSettingsPage = new SettingsPage();

	$(document).ready(function () {
		app.wsbpSettingsPage.init();
	});

}(window.jQuery, window));


