(function ($, app) {
"use strict";
	function ActionsPage() {
		this.$obj = this;
		return this.$obj;
	}
	
	ActionsPage.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = WSBP_DATA.isPro == '1';
		_this.dateFormat = WSBP_DATA.dateFormat;
		_this.timeFormat = WSBP_DATA.timeFormat;
		_this.usersBlock = $('.wupsales-table-list');
		_this.filtersBlock = $('#wsbpUsersFilter');
		_this.filterId = 0;
		_this.langSettings = wsbpParseJSON($('#wsbpLangSettingsJson').val());
		$('#wspb-custom-css').appendTo('head');
		
		_this.eventsActionsPage();
		wsbpInitCheckAll(_this.usersBlock);
		if (typeof(_this.initPro) == 'function') _this.initPro();
		_this.initUsersList();
		_this.initHistoryList();
	}
	
	ActionsPage.prototype.eventsActionsPage = function () {
		var _this = this.$obj,
			$mainTabs = $('.wsbp-main-tabs .button'),
			$mainTabsContent = $('.wsbp-main-tab-content > .block-tab'),
			$curTab = $mainTabs.filter('.current'),
			$controls = $('.wupsales-control-buttons');
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
			
			if ($this.data('model') == 'users') $controls.removeClass('wupsales-hidden');
			else $controls.addClass('wupsales-hidden');
			
		});
		_this.currentActionId = 0;
		_this.currentActionStatus = 0;
		_this.currentActionParams = false;
		_this.currentUserFilter = false;
		_this.currentUserAdd = true;
		_this.currentUserIds = [];
		_this.infoPopup = false;
		_this.infoPopupUserId = 0;
		_this.emailPopup = false;

		_this.filtersBlock.find('#wsbpAddFilter').on('click', function(e) {
			e.preventDefault();
			_this.filterId++;
			var $button = $(this),
				filterType = _this.filtersBlock.find('#wsbpUsersCondTypes').val(),
				filterNum = '[' + _this.filterId + ']',
				filterBlock = $('#wsbp-filter-'+filterType).clone().removeAttr('id').addClass('wsbp-new-filter');
			filterBlock.find('[name^="filters[N]"]').each(function() {
				$(this).attr('name', $(this).attr('name').replace('[N]', filterNum));
			});
			_this.filtersBlock.find('.wsbp-filters-list').append(filterBlock);
			filterBlock = _this.filtersBlock.find('.wsbp-new-filter');
			filterBlock.find('select.wupsales-chosen').removeClass('no-chosen');
			wsbpInitMultySelects(filterBlock);
			filterBlock.find('.wsbp-field-date').datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: _this.dateFormat,
				showAnim: '',
			});
				
			filterBlock.removeClass('wsbp-new-filter');
			return false;
		});
		_this.filtersBlock.on('click', '.wsbp-delete-cond', function(e) {
			e.preventDefault();
			$(this).closest('.row-options-block').remove();
			return false;
		});
		_this.attrValues = wsbpParseJSON(_this.filtersBlock.find('#wsbpAttrValuesJson').val());
		_this.filtersBlock.on('change', '.wsbp-attribute-slug', function() {
			var $select = $(this),
				slug = $select.val(),
				$valueWraper = $select.closest('.row-options-block').find('.wsbp-attribute-value');
			_this.setAttributeValues(slug, $valueWraper, true);
		});
			
		var dAddPoint = $('#wsbpDialogAddPoint');

		_this.dialogAddPoint = dAddPoint.dialog({
			position: {my: 'center', at: 'center', of: '.wupsales-main'},
			autoOpen: false,
			width: 500,
			height: 'auto',
			modal: true,
			dialogClass: 'wupsales-plugin',
			classes: {
				'ui-dialog': 'wupsales-plugin'
			},
			buttons: [
				{
					text: wsbpCheckSettings(_this.langSettings, 'btn-save'),
					class: 'button button-secondary wsbp-btn-save',
					click: function() {
						var $this = $(this);
						dAddPoint.find('.wsbp-field-html').each(function() {
							var $field = $(this);
							$field.val(tinyMCE.get($field.attr('id')).getContent());
						});
						$.sendFormWsbp({
							btn: $this.parent().find('.wsbp-btn-save'),
							data: {
								mod: 'actions',
								action: 'saveUserAction',
								actionId: _this.currentActionId,
								params: jsonInputsWsbp($this, true),
								conditions: _this.currentActionId ? false : 
									(_this.currentUserFilter ? jsonInputsWsbp(_this.filtersBlock, true) : {ids: _this.currentUserIds})
							},
							onSuccess: function(res) {
								if (!res.error) {
									$this.dialog('close');
									_this.wsbpUsersTable.ajax.reload();
									_this.wsbpHistoryTable.ajax.reload();
								}
							}
						});
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
					isEdit = _this.currentActionStatus == 0,
					bntSave = $this.parent().find('.wsbp-btn-save');
				$this.find('input, select, textarea').prop('disabled', isEdit ? false : true);
				if (isEdit) bntSave.removeClass('wupsales-hidden');
				else bntSave.addClass('wupsales-hidden');
				
				$this.find('input, select, textarea').each(function() {
					var $elem = $(this),
						name = $elem.attr('name');
					if (name && name.length) {
						var value = $elem.attr('data-default');
						if (_this.currentActionParams) {
							if (name in _this.currentActionParams) value = _this.currentActionParams[name];
							else {
								var pos = name.indexOf('[');
								if (pos > -1) {
									var n = name.substring(0, pos),
										s = name.substring(pos + 1, name.length - 1);
									if (n in _this.currentActionParams && s in _this.currentActionParams[n]) value = _this.currentActionParams[n][s];
								}
							}
						}
						if($elem.is('select')) $elem.val(value);
						else if($elem.is('input[type="checkbox"]')) $elem.prop('checked', $elem.is('[value="' + value + '"]'));
						else $elem.val(value);
						$elem.trigger('wsbp-change');
						if ($elem.hasClass('wsbp-field-html')) tinyMCE.get($elem.attr('id')).setContent($elem.val());
					}
				});
				if (_this.currentActionId == 0 && !_this.currentUserAdd) {
					$this.find('select[name="operation"]').val('del').trigger('wsbp-change');
				}
			},
			create: function( event, ui ) {
				var $this = $(this);
				$this.parent().css('maxWidth', $(window).width()+'px');
				$this.parent().find('.wsbp-btn-save').prepend($('<i class="fa fa-floppy-o" aria-hidden="true"></i>'));
			}
		});
		dAddPoint.find('.wsbp-field-date').datetimepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: _this.dateFormat,
			timeFormat: _this.timeFormat,
			showAnim: '',
		});
		
		dAddPoint.on('change wsbp-change', 'input[type="checkbox"]', function () {
			var elem = jQuery(this),
				name = elem.attr('name'),
				childrens = dAddPoint.find('[data-parent="' + name + '"]');
			if (childrens.length) {
				if (elem.is(':checked')) {
					childrens.removeClass('wupsales-hidden');
					childrens.find('select,input[type="checkbox"]').trigger('wsbp-change');
				} else childrens.addClass('wupsales-hidden');
			}
		});
		dAddPoint.on('change wsbp-change', 'select', function () {
			var elem = jQuery(this),
				value = elem.val(),
				name = elem.attr('name'),
				subOptions = dAddPoint.find('[data-select="' + name + '"]');
			if(subOptions.length) {
				subOptions.addClass('wupsales-hidden');
				subOptions.filter('[data-select-value*="'+value+'"]').removeClass('wupsales-hidden');
			}
		});
		dAddPoint.find('.wsbp-field-html').each(function() {
			var $field = $(this),
				fieldId = $field.attr('id'),
				editorSettings = {
					selector: '#' + fieldId,
					mediaButtons: true,
					quicktags: true,
					tinymce: {
						wpautop: true,
						toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,forecolor,fontsizeselect,link,undo,redo,wp_help',
						height: 200
					}
				};

			wp.editor.remove(fieldId);
			wp.editor.initialize(fieldId, editorSettings);
			tinyMCE.get(fieldId).setContent($field.val());
			_this.mediaMCE = true;
		});
		dAddPoint.find('#wsbpEmailPreview').on('click', function() {
			if (!_this.emailPopup) {
				_this.emailPopup = $('<div class="wsbp-widget-popup wsbp-info-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content wupsales-plugin"><div class="wsbp-popup-loader"></div></div></div>');
				_this.emailPopup.appendTo($('body'));
				
			}
			dAddPoint.find('.wsbp-field-html').each(function() {
				var $field = $(this);
				$field.val(tinyMCE.get($field.attr('id')).getContent());
			});
			$.sendFormWsbp({
				data: {
					mod: 'actions', 
					action: 'getEmailPreview',
					params: _this.currentActionStatus == 0 ? jsonInputsWsbp(dAddPoint.find('#wsbpEmailSettings'), true) : _this.currentActionParams
				},
				onSuccess: function(res) {
					if (!res.error && res.html) {
						_this.emailPopupLoaded(res.html);
					}
				}
			});
			_this.emailPopup.bPopup({
				closeClass:'wsbp-b-close',
				speed: 450,
				transition: 'fadeIn',
				onClose: function(){ 
					_this.emailPopup.find('.wsbp-popup-content').html('<div class="wsbp-popup-loader"></div>');
				}
			});
		});
		dAddPoint.find('#wsbpPopupPreview').on('click', function() {
			if (!_this.emailPopup) {
				_this.emailPopup = $('<div class="wsbp-widget-popup wsbp-info-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content wupsales-plugin"><div class="wsbp-popup-loader"></div></div></div>');
				_this.emailPopup.appendTo($('body'));
				
			}
			dAddPoint.find('.wsbp-field-html').each(function() {
				var $field = $(this);
				$field.val(tinyMCE.get($field.attr('id')).getContent());
			});
			$.sendFormWsbp({
				data: {
					mod: 'actions', 
					action: 'getPopupPreview',
					params: _this.currentActionStatus == 0 ? jsonInputsWsbp(dAddPoint.find('#wsbpPopupSettings'), true) : _this.currentActionParams
				},
				onSuccess: function(res) {
					if (!res.error && res.html) {
						_this.emailPopupLoaded(res.html);
					}
				}
			});
			_this.emailPopup.bPopup({
				closeClass:'wsbp-b-close',
				speed: 450,
				transition: 'fadeIn',
				onClose: function(){ 
					_this.emailPopup.find('.wsbp-popup-content').html('<div class="wsbp-popup-loader"></div>');
				}
			});
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
						var inCron = dRecalc.find('input[name="in_cron"]').is(':checked')
						$.sendFormWsbp({
							btn: $('#wsbpBtnRecalc'),
							data: {
								mod: 'actions', 
								action: 'recalcUsersBalance', 
								inCron: inCron ? 1 : 0
							},
							onSuccess: function(res) {
								if (!res.error && !inCron) {
									setTimeout(function() {
										_this.wsbpUsersTable.ajax.reload();
									}, 500);
								}
							}
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
		
		$('#wsbpBtnFilter').click(function(){
			_this.wsbpUsersTable.ajax.reload();
			return false;
		});
		
		$('#wsbpBtnRecalc').click(function(){
			_this.dialogRecalc.dialog('open');
			return false;
		});
		
		$('body').on('click', '.tooltipster-content button', function () {
			var $this = $(this),
				content = $this.closest('.tooltipster-content');
			if ($this.hasClass('wsbp-lock') || $this.hasClass('wsbp-unlock')) {
				var id = content.find('.wupsales-hidden').html(),
					actName = $this.hasClass('wsbp-lock') ? 'lock' : 'unlock';
				if (id.length) {
					$.sendFormWsbp({
						icon: _this.wsbpUsers.find('.wupsales-list-actions[data-id="' + id +'"] i.wsbp-' + actName),
						data: {
							mod: 'actions',
							action: 'lockUser',
							userId: id,
							lock: actName,
						},
						onSuccess: function(res) {
							if (!res.error) {
								setTimeout(function() {
									_this.wsbpUsersTable.ajax.reload();
								}, 500);
							}
						}
					});
				}
			} else if ($this.hasClass('wsbp-delete')) {
				var id = content.find('.wupsales-hidden').html();
				if (id.length) {
					$.sendFormWsbp({
						icon: _this.wsbpHistory.find('.wupsales-list-actions[data-id="' + id +'"] i.wsbp-delete'),
						data: {
							mod: 'actions',
							action: 'deleteAction',
							actionId: id,
						},
						onSuccess: function(res) {
							if (!res.error) {
								setTimeout(function() {
									_this.wsbpHistoryTable.ajax.reload();
								}, 500);
							}
						}
					});
				}
			}
			content.parent().removeClass('tooltipster-fade-show');
		});
	}
	
	ActionsPage.prototype.setAttributeValues = function (slug, $valueWraper, update) {
		var _this = this.$obj;
				
		if (slug == '') $valueWraper.addClass('wupsales-hidden');
		else {
			var $multy = $valueWraper.find('select');
			$multy.find('option').remove();
			if (_this.attrValues && _this.attrValues[slug]) {
				for (var id in _this.attrValues[slug]) {
					$multy.append($('<option value="'+id+'">'+_this.attrValues[slug][id]+'</option>'));
				}
			}
			if (update) $multy.trigger("chosen:updated");
			$valueWraper.removeClass('wupsales-hidden');
		}
	}
	
	ActionsPage.prototype.initUsersList = function () {
		var _this = this.$obj,
			wsbpUsers = $('#wsbpUsersList'),
			url = wsbpGetAjaxUrl();
		_this.wsbpUsers = wsbpUsers;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small wupsales-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'wupsales-flat-input';
		
		_this.wsbpUsersTable = wsbpUsers.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				url: url + '?mod=actions&action=getUsersList&pl=wsbp&reqType=ajax',
				type: 'POST',
				data: function (d) {
					d.filters = jsonInputsWsbp(_this.filtersBlock, true);
				}
			},
			lengthChange: true,
			lengthMenu: [ [10, 20, 50, -1], [10, 20, 50, "All"] ],
			paging: true,
			dom: 'B<"pull-right"fl>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			buttons: [
				{
					text: '<i class="fa fa-fw fa-plus"></i>' + wsbpCheckSettings(_this.langSettings, 'btn-add'),
					className: 'button button-mini button-secondary wsbp-points-set',
					action: function (e, dt, node, config) {
						var ids = [];
						_this.wsbpUsers.find('.wsbpCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));
						});
						if (ids.length) {
							_this.currentActionId = 0;
							_this.currentActionStatus = 0;
							_this.currentUserFilter = false;
							_this.currentUserAdd = true;
							_this.currentUserIds = ids;
							_this.dialogAddPoint.dialog('open');
						} else {
							jQuery.sNotify({
								'icon': 'fa fa-exclamation-circle',
								'error': true,
								'content': '<span> '+wsbpCheckSettings(_this.langSettings, 'err-add')+'</span>',
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
						_this.wsbpUsers.find('.wsbpCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));
						});
						if (ids.length) {
							_this.currentActionId = 0;
							_this.currentActionStatus = 0;
							_this.currentUserFilter = false;
							_this.currentUserAdd = false;
							_this.currentUserIds = ids;
							_this.dialogAddPoint.dialog('open');
						} else {
							jQuery.sNotify({
								'icon': 'fa fa-exclamation-circle',
								'error': true,
								'content': '<span> '+wsbpCheckSettings(_this.langSettings, 'err-delete')+'</span>',
								'delay': 3000,
								'left': 'center'
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-filter"></i>' + wsbpCheckSettings(_this.langSettings, 'btn-filtered'),
					className: 'button button-mini wsbp-points-delete',
					action: function (e, dt, node, config) {
						_this.currentActionId = 0;
						_this.currentActionStatus = 0;
						_this.currentUserFilter = true;
						_this.currentUserAdd = true;
						_this.dialogAddPoint.dialog('open');
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
					targets: 5
				},
				{
					width: "80px",
					targets: 6
				},
				{
					"orderable": false,
					targets: [0, 6]
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
				$('#wsbpUsersList_wrapper .dataTables_paginate')[0].style.display = $('#wsbpUsersList_wrapper .dataTables_paginate  span .wupsales-paginate').size() > 1 ? 'block' : 'none';
				wsbpInitTooltips('#wsbpUsersList');
				wsbpUsers.find('.wsbpCheckAll').prop('checked', false);
				
			}
		});

		wsbpUsers.on('click', '.wupsales-list-actions i', function() {
			var $this = $(this);
			if ($this.hasClass('wsbp-add') || $this.hasClass('wsbp-delete')) {
				_this.currentActionId = 0;
				_this.currentActionStatus = 0;
				_this.currentUserFilter = false;
				_this.currentUserAdd = $this.hasClass('wsbp-add');
				_this.currentUserIds = [$this.closest('.wupsales-list-actions').attr('data-id')];
				_this.dialogAddPoint.dialog('open');
			} else if ($this.hasClass('wsbp-info')) {
				if (!_this.infoPopup) {
					_this.infoPopup = $('<div class="wsbp-widget-popup"><span class="button wsbp-b-close"><i class="fa fa-times" aria-hidden="true"></i></span><div class="wsbp-popup-content wupsales-plugin"></div></div>');
					_this.infoPopup.appendTo($('body'));
				}
				var userId = $this.closest('.wupsales-list-actions').attr('data-id');
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
	ActionsPage.prototype.emailPopupLoaded = function (data) {
		var _this = this.$obj;
		if (!_this.emailPopup || !data) return;
		
		_this.emailPopup.find('.wsbp-popup-content').html(data);
	}
	ActionsPage.prototype.infoPopupLoaded = function (data) {
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
	
	ActionsPage.prototype.initHistoryList = function () {
		var _this = this.$obj,
			wsbpHistory = $('#wsbpHistoryList'),
			url = wsbpGetAjaxUrl();
		_this.wsbpHistory = wsbpHistory;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small wupsales-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'wupsales-flat-input';
		
		_this.wsbpHistoryTable = wsbpHistory.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				url: url + '?mod=actions&action=getHistoryList&pl=wsbp&reqType=ajax',
				type: 'POST',
				data: function (d) {
					d.completed = $('#wsbpShowCompleted').is(':checked') ? 1 : 0;
				}
			},
			lengthChange: true,
			lengthMenu: [ [10, 20, 50, -1], [10, 20, 50, "All"] ],
			paging: true,
			dom: '<"pull-right"fl>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			columnDefs: [
				{
					className: "dt-left",
					width: "20px",
					targets: 0
				},
				{
					width: "80px",
					targets: 1
				},
				{
					width: "80px",
					targets: 2
				},
				{
					width: "50px",
					targets: 4
				},
				{
					width: "80px",
					targets: 7
				},
				{
					"orderable": false,
					targets: [2, 5, 7]
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
				$('#wsbpHistoryList_wrapper .dataTables_paginate')[0].style.display = $('#wsbpHistoryList_wrapper .dataTables_paginate  span .wupsales-paginate').size() > 1 ? 'block' : 'none';
				wsbpInitTooltips('#wsbpHistoryList');
			}
		});
		
		$('#wsbpShowCompleted').on('change', function() {
			_this.wsbpHistoryTable.ajax.reload();
		});
		
		wsbpHistory.on('click', '.wupsales-list-actions i', function() {
			var $this = $(this);
			if ($this.hasClass('wsbp-edit') || $this.hasClass('wsbp-view')) {
				_this.currentActionId = $this.closest('.wupsales-list-actions').attr('data-id');
				_this.currentActionStatus = $this.hasClass('wsbp-edit') ? 0 : 1;
				_this.currentActionParams = wsbpParseJSON($this.closest('.wupsales-list-actions').find('input[name="params"]').val());
				_this.dialogAddPoint.dialog('open');
			} 
		});
		
	}
	
	app.wsbpActionsPage = new ActionsPage();

	$(document).ready(function () {
		app.wsbpActionsPage.init();
	});

}(window.jQuery, window));


