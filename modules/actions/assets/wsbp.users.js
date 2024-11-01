(function ($, app) {
"use strict";
	function WidgetUsers() {
		this.$obj = this;
		return this.$obj;
	}
	
	WidgetUsers.prototype.init = function () {
		var _this = this.$obj;
		_this.langSettings = wsbpParseJSON($('#wsbpLangSettingsJson').val());
		_this.filtersBlock = $('#wsbpUsersWidgetFilter');
		_this.tableBlock = $('#wsbpWidgetUsersList');
		_this.actionPopup = false;
		_this.userPopup = false;
		_this.currentUserId = 0;
		_this.infoPopupUserId = 0;
		
		_this.initUsersList();
		_this.eventsWidgetUsers();
	}
	WidgetUsers.prototype.eventsWidgetUsers = function () {
		var _this = this.$obj;
		
		$('#wsbpWidgetUserBtnFilter').off('click').on('click', function(e){
			e.preventDefault();
			_this.wsbpUsersTable.ajax.reload();
			return false;
		});
		_this.filtersBlock.find('form').off('submit').on('submit', function(e){
			e.preventDefault();
			_this.wsbpUsersTable.ajax.reload();
			return false;
		});
		
		_this.tableBlock.on('click', '.wsbp-list-actions i', function() {
			var $this = $(this),
				userId = $this.closest('div').attr('data-id'),
				userName = $this.closest('tr').find('.wsbp-user-name').html();
			if ($this.hasClass('wsbp-add')) {
				if ($this.closest('div.wsbp-user-active').length) {
					_this.currentUserId = userId;
					_this.showPointsPopup(userName);
				}
			} else if ($this.hasClass('wsbp-edit')) {
				_this.currentUserId = userId;
				_this.showUserPopup(userName);
			} else if ($this.hasClass('wsbp-info')) {
				if (!_this.infoPopup) {
					_this.infoPopup = $('<div class="wsbp-widget-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content wupsales-plugin"></div></div>');
					_this.infoPopup.appendTo($('body'));
				}
				if (_this.infoPopupUserId != userId) {
					_this.infoPopup.find('.wsbp-popup-content').html('<div class="wsbp-popup-loader"></div>');
					$.sendFormWsbp({
						data: {
							mod: 'bonuses', 
							action: 'getWidgetPopup',
							userId: userId
						},
						onSuccess: function(res) {
							if (!res.error && res.data) {
								_this.infoPopupLoaded(res.data);
								_this.infoPopupUserId = userId;
							}
						}
					});
				}
				_this.infoPopup.bPopup({
					closeClass:'wsbp-b-close',
					speed: 450,
					transition: 'slideDown'
					});
				return false;
			}
		});
	}
	WidgetUsers.prototype.showPointsPopup = function(name) {
		var _this = this.$obj;
		if (!_this.actionPopup) {
			_this.actionPopup = $('<div class="wsbp-widget-popup wsbp-user-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content"></div></div>');
			_this.actionPopup.appendTo($('body'));
			$('#wsbpDialogAddPoint').appendTo(_this.actionPopup.find('.wsbp-popup-content'));
			_this.actionPopup.on('click', 'button.wsbp-cancel-action', function() {
				_this.actionPopup.find('.wsbp-b-close').trigger('click');
			});
			
			_this.actionPopup.on('click', 'button.wsbp-save-action', function() {
				var $this = $(this);
				$.sendFormWsbp({
					btn: $this,
					data: {
						mod: 'actions',
						action: 'saveUserAction',
						actionId: 0,
						actionWidget: 1,
						wsbpNonce: WSBP_FRONT.wsbpNonce,
						params: jsonInputsWsbp(_this.actionPopup, true),
						conditions: {ids: [_this.currentUserId]}
					},
					onSuccess: function(res) {
						if (!res.error) {
							_this.actionPopup.find('.wsbp-b-close').trigger('click');
							_this.wsbpUsersTable.ajax.reload();
						}
					}
				});
			});
			_this.actionPopup.on('change wsbp-change', 'select', function () {
				var elem = jQuery(this),
					value = elem.val(),
					name = elem.attr('name'),
					subOptions = _this.actionPopup.find('[data-select="' + name + '"]');
				if(subOptions.length) {
					subOptions.addClass('wsbp-hidden');
					subOptions.filter('[data-select-value*="'+value+'"]').removeClass('wsbp-hidden');
				}
			});
		}
		_this.actionPopup.find('.wsbp-label-name').html(name);
		_this.actionPopup.find('select').trigger('wsbp-change');
		_this.actionPopup.bPopup({
			closeClass: 'wsbp-b-close',
			speed: 450,
			transition: 'slideIn'
		});
	}
	WidgetUsers.prototype.showUserPopup = function(name) {
		var _this = this.$obj;
		if (!_this.userPopup) {
			_this.userPopup = $('<div class="wsbp-widget-popup wsbp-user-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content"></div></div>');
			_this.userPopup.appendTo($('body'));
			$('#wsbpDialogAddUser').appendTo(_this.userPopup.find('.wsbp-popup-content'));
			_this.userPopup.on('click', 'button.wsbp-cancel-action', function() {
				_this.userPopup.find('.wsbp-b-close').trigger('click');
			});
			
			_this.userPopup.on('click', 'button.wsbp-save-action', function() {
				var $this = $(this);
				$.sendFormWsbp({
					btn: $this,
					data: {
						mod: 'actions',
						action: 'addNewUser',
						wsbpNonce: WSBP_FRONT.wsbpNonce,
						userId: _this.currentUserId,
						params: jsonInputsWsbp(_this.userPopup, true)
					},
					onSuccess: function(res) {
						if (!res.error) {
							_this.userPopup.find('.wsbp-b-close').trigger('click');
							_this.wsbpUsersTable.ajax.reload();
						}
					}
				});
			});
		}
		var $fields = _this.userPopup.find('.wsbp-popup-content').find('input, select');
		$fields.each(function() {
			var $this = $(this),
				def = $this.attr('data-default');
			if (def) $this.val(def);
			else $this.val('');
		});
		var $label = _this.userPopup.find('.wsbp-label-name.wspb-user-name');
		_this.userPopup.find('.wsbp-popup-loader').remove();
		if (_this.currentUserId) {
			$('#wsbpDialogAddUser').addClass('wsbp-hidden');
			$('<div class="wsbp-popup-loader"></div>').appendTo(_this.userPopup.find('.wsbp-popup-content'));
			$.sendFormWsbp({
				data: {
					mod: 'actions', 
					action: 'getUserData',
					userId: _this.currentUserId
				},
				onSuccess: function(res) {
					if (!res.error && res.data) {
						$label.html(name);
						$fields.each(function() {
							var $this = $(this),
								field = $this.attr('name');
							if (field in res.data) {
								$this.val(res.data[field]);
							}
						});
						
						_this.userPopup.find('.wsbp-popup-loader').remove();
						$('#wsbpDialogAddUser').removeClass('wsbp-hidden');
					}
				}
			});
		} else {
			$('#wsbpDialogAddUser').removeClass('wsbp-hidden');
			$label.html($label.attr('data-label-new'));
		}

		_this.userPopup.bPopup({
			closeClass: 'wsbp-b-close',
			speed: 450,
			transition: 'slideOut'
		});
	}
	
	WidgetUsers.prototype.initUsersList = function () {
		var _this = this.$obj,
			wsbpUsers = $('#wsbpWidgetUsersList'),
			url = wsbpGetAjaxUrl();
		_this.wsbpUsers = wsbpUsers;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small wupsales-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'wupsales-flat-input';
		
		_this.wsbpUsersTable = wsbpUsers.DataTable({
			serverSide: true,
			processing: true,
			deferLoading: false,
			ajax: {
				url: url + '?mod=actions&action=getWidgetUsersList&pl=wsbp&reqType=ajax&wsbpNonce='+WSBP_FRONT.wsbpNonce,
				type: 'POST',
				data: function (d) {
					d.filters = jsonInputsWsbp(_this.filtersBlock, true);
				}
			},
			lengthChange: true,
			lengthMenu: [ [10, 50, 100], [10, 50, 100] ],
			paging: true,
			dom: 'B<"pull-right"l>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			buttons: [
				{
					text: '<i class="fa fa-fw fa-plus"></i>' + wsbpCheckSettings(_this.langSettings, 'btn-add'),
					className: 'button wsbp-add-new-user',
					action: function (e, dt, node, config) {
						_this.currentUserId = 0;
						_this.showUserPopup(false);
						return false;
					}
				},
			],
			columnDefs: [
				{
					className: "dt-left",
					targets: [0,1,3]
				},
				{
					className: "dt-right",
					targets: [2,4]
				},
				{
					"orderable": false,
					targets: [3,4]
				}
			],
			order: [[ 0, 'asc' ]],
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
				$('#wsbpWidgetUsersList_wrapper .dataTables_paginate')[0].style.display = $('#wsbpWidgetUsersList_wrapper .dataTables_paginate  span .wupsales-paginate').length > 1 ? 'block' : 'none';
			}
		});
	}
	WidgetUsers.prototype.infoPopupLoaded = function (data) {
		var _this = this.$obj;
		if (!_this.infoPopup || !data.html) return;
		if (data.css) $(data.css).appendTo('head');
		
		_this.infoPopup.find('.wsbp-popup-content').html(data.html);
		
		var $mainTabs = _this.infoPopup.find('.wsbp-widget-tabs .wsbp-widget-tab'),
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
		_this.infoPopup.find('.wsbp-toggle').on('click', function(e){
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
		_this.infoPopup.find('.wsbp-field-date').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: _this.dateFormat,
			showAnim: '',
			yearRange: "-100:+0"
		});
		var $filter = _this.infoPopup.find('.wsbp-trans-filter'),
			$table = _this.infoPopup.find('.wsbp-widget-trans');
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
	}

	app.wsbpWidgetUsers = new WidgetUsers();

	$(document).ready(function () {
		app.wsbpWidgetUsers.init();
	});
}(window.jQuery, window));