(function ($, app) {
"use strict";
	function BonusesPage() {
		this.$obj = this;
		return this.$obj;
	}
	
	BonusesPage.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = WSBP_DATA.isPro == '1';
		_this.productsBlock = $('.wupsales-table-list');
		_this.langSettings = wsbpParseJSON($('#wsbpLangSettingsJson').val());
		
		_this.eventsBonusesPage();
		wsbpInitCheckAll(_this.productsBlock);
		if (typeof(_this.initPro) == 'function') _this.initPro();
		_this.initProductsList();
	}
	
	BonusesPage.prototype.eventsBonusesPage = function () {
		var _this = this.$obj,
			$mainTabs = $('.wsbp-main-tabs .button'),
			$mainTabsContent = $('.wsbp-main-tab-content > .block-tab'),
			$curTab = $mainTabs.filter('.current'),
			$controls = $('.wupsales-control-buttons .group-button');
		$mainTabsContent.filter($curTab.attr('href')).addClass('active');

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
			
			if ($this.data('model') == 'groups') $controls.removeClass('wupsales-hidden');
			else $controls.addClass('wupsales-hidden');
		});
		
		_this.currentPointTd = '';
		_this.currentPointIds = [];

		_this.productsBlock.on('click', 'td.wsbp-set-point', function() {
			_this.currentPointTd = $(this);
			_this.dialogSetPoint.dialog('open');
		});
		_this.productsBlock.on('click', 'td.wsbp-product-name', function() {
			_this.currentPointTd = $(this).closest('tr').find('.wsbp-set-point');
			_this.dialogSetPoint.dialog('open');
		});
	
		$('#wsbpBtnSave').click(function(){
			$('#wsbpBonusesForm').submit();
				return false;
		});
		$('#wsbpBonusesForm').submit(function(){
			_this.beforeSave();
			$(this).sendFormWsbp({
				btn: $('#wsbpBtnSave')
			});
			return false;
		});
		
		var dRecalc = $('#wsbpDialogRecalc');

		_this.dialogRecalc = dRecalc.dialog({
			position: {my: 'center', at: 'center', of: '.wupsales-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 600,
			height: 'auto',
			modal: true,
			dialogClass: 'wupsales-plugin',
			classes: {
				'ui-dialog': 'wupsales-plugin'
			},
			buttons: [
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-run'),
					class: 'button button-secondary',
					click: function() {
						var inCron = dRecalc.find('input[name="in_cron"]').is(':checked');
						$.sendFormWsbp({
							btn: $('#wsbpBtnRecalc'),
							data: {
								mod: 'bonuses', 
								action: 'recalcProductsPoints', 
								inCron: inCron ? 1 : 0
							},
						});
						$(this).dialog('close');
					}
				},
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-cancel'),
					class: 'button button-minor',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});

		$('#wsbpBtnRecalc').click(function(){
			_this.dialogRecalc.dialog('open');
			return false;
		});
		
	}
	
	BonusesPage.prototype.initProductsList = function () {
		var _this = this.$obj,
			wsbpProducts = $('#wsbpProductsList'),
			url = wsbpGetAjaxUrl();
		_this.wsbpProducts = wsbpProducts;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small wupsales-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'wupsales-flat-input';
		
		_this.wsbpProductsTable = wsbpProducts.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				url: url + '?mod=bonuses&action=getProductsList&pl=wsbp&reqType=ajax',
				type: 'POST',
				data: function (d) {
					d.variations = $('#wsbpShowVariations').is(':checked') ? 1 : 0;
				}
			},
			lengthChange: true,
			lengthMenu: [ [10, 50, 100, 300, -1], [10, 50, 100, 300, "All"] ],
			paging: true,
			dom: 'B<"pull-right"fl>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			buttons: [
				{
					text: '<i class="fa fa-fw fa-plus"></i>' + wsbpCheckSettings(_this.langSettings, 'btn-set'),
					className: 'button button-mini wsbp-points-set',
					action: function (e, dt, node, config) {
						var ids = [];
						_this.wsbpProducts.find('.wsbpCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));
						});
						if (ids.length) {
							_this.currentPointTd = false;
							_this.currentPointIds = ids;
							_this.dialogSetPoint.dialog('open');
						} else {
							jQuery.sNotify({
								'icon': 'fa fa-exclamation-circle',
								'error': true,
								'content': '<span> '+wsbpCheckSettings(_this.langSettings, 'err-set')+'</span>',
								'delay': 3000,
								'left': 'center'
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-trash-o"></i>' + wsbpCheckSettings(_this.langSettings, 'btn-delete'),
					className: 'button button-mini button-alert wsbp-points-delete',
					action: function (e, dt, node, config) {
						var ids = [];
						_this.wsbpProducts.find('.wsbpCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));
						});
						if (ids.length == 0) {
							jQuery.sNotify({
								'icon': 'fa fa-exclamation-circle',
								'error': true,
								'content': '<span> '+wsbpCheckSettings(_this.langSettings, 'err-clear')+'</span>',
								'delay': 3000,
								'left': 'center'
							});
						} else if (confirm(wsbpCheckSettings(_this.langSettings, 'confirm-delete'))) {
							_this.currentPointTd = false;
							_this.saveProductPoints(ids, '');
						}
					}
				}
			],
			columnDefs: [
				{
					className: "dt-left",
					width: "20px",
					targets: 0
				},
				{
					width: "20px",
					targets: 1
				},
				{
					width: "50px",
					targets: 3
				},
				{ className: "wsbp-product-name", targets: [2] },
				{ className: "wsbp-set-point", targets: [3] },
				{
					"orderable": false,
					targets: [0, 3]
				}
			],
			order: [[ 1, 'desc' ]],
			language: {
				emptyTable: wsbpCheckSettings(_this.langSettings, 'emptyTable'),
				paginate: {
					next: '<i class="fa fa-fw fa-angle-right">',
					previous: '<i class="fa fa-fw fa-angle-left">'  
				},
				lengthMenu: wsbpCheckSettings(_this.langSettings, 'lengthMenu') + ' _MENU_',
				info: wsbpCheckSettings(_this.langSettings, 'info') + ' _START_ to _END_ of _TOTAL_',
				search: '_INPUT_'
			},
			fnDrawCallback : function() {
				$('#wsbpProductsList_wrapper .dataTables_paginate')[0].style.display = $('#wsbpProductsList_wrapper .dataTables_paginate  span .wupsales-paginate').size() > 1 ? 'block' : 'none';
				wsbpInitTooltips('#wsbpProductsList');
				wsbpProducts.find('.wsbpCheckAll').prop('checked', false);
			}
		});
		
		$('#wsbpShowVariations').on('change', function() {
			_this.wsbpProductsTable.ajax.reload();
		});
		
		var dSetPoint = $('#wsbpDialogSetPoint');

		_this.dialogSetPoint = dSetPoint.dialog({
			position: {my: 'center', at: 'center', of: '.wupsales-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 300,
			height: 'auto',
			modal: true,
			dialogClass: 'wupsales-plugin',
			classes: {
				'ui-dialog': 'wupsales-plugin'
			},
			buttons: [
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-set'),
					class: 'button button-secondary',
					click: function() {
						var $this = $(this),
							point = $this.find('.wsbp-input-point').val();
						point.replace(' ', '');
						if (point.length) point += $this.find('.wsbp-select-point').val();
						else point = '';
						_this.saveProductPoints(_this.currentPointTd ? [_this.currentPointTd.closest('tr').find('.wsbpCheckOne').attr('data-id')] : _this.currentPointIds, point);
						
						$this.dialog('close');
					}
				},
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-delete'),
					class: 'button button-alert',
					click: function() {
						_this.saveProductPoints(_this.currentPointTd ? [_this.currentPointTd.closest('tr').find('.wsbpCheckOne').attr('data-id')] : _this.currentPointIds, '');
						$(this).dialog('close');
					}
				},
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-cancel'),
					class: 'button button-minor',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			open: function() {
				var $this = $(this),
					$select = $this.find('.wsbp-select-point'),
					perc = $select.attr('data-default'),
					point = _this.currentPointTd ? _this.currentPointTd.html() : '-';
				if (point == '-') point = '';
				else if (point.indexOf('%') == -1) perc = '';
				else {
					perc = '%';
					point = point.replace('%', '');
				}
				$this.find('.wsbp-input-point').val(point);
				$select.val(perc);
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});
	}
	
	BonusesPage.prototype.saveProductPoints = function (ids, point) {
		var _this = this.$obj,
			loader = $('#wsbpProductsList_processing');
		loader.show();
		$.sendFormWsbp({
			data: {mod: 'bonuses', action: 'setProductPoints', ids: ids, point: point},
			onSuccess: function(res) {
				if (!res.error) {
					var point = res.point;
					if (point.length == 0) point = '-';
					for (var i = 0; i < ids.length; i++) {
						var $check = _this.wsbpProducts.find('.wsbpCheckOne[data-id="'+ids[i]+'"]').prop('checked', false);
						$check.closest('tr').find('.wsbp-set-point').html(point);
					}
					_this.wsbpProducts.find('.wsbpCheckAll').prop('checked', false);
				}
				loader.hide();
			}
		});
	}
	BonusesPage.prototype.beforeSave = function () {
		var _this = this.$obj;
		if (typeof(_this.beforeSavePro) == 'function') _this.beforeSavePro();
	}
	
	app.wsbpBonusesPage = new BonusesPage();

	$(document).ready(function () {
		app.wsbpBonusesPage.init();
	});

}(window.jQuery, window));
