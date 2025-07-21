(function ($){
	class FaehreBelgernSettings {
		private $dateFieldPlanned!: JQuery;

		constructor() {
			this.initElements();
			this.bindEvents();
			this.initDatePicker();
		}

		private initElements(): void {
			this.$dateFieldPlanned = $('#planned_date');
		}

		private bindEvents(): void {
			$('.edit-current-status').on('click', (e) => this.showEditForm(e, false));
			$('.edit-planned-status, .add-planned-status').on('click', (e) => this.showEditForm(e, true));
			$('.cancel-edit-status, .cancel-edit-planned').on('click', (e) => this.hideEditForm(e));
			$('.save-current-status, .save-planned-status').on('click', (e) => this.handleSave(e));
			$('#current_status, #planned_status').on('change', (e) => this.updateDefaultComment(e));
			$('.use-default-comment, .use-planned-default-comment').on('click', (e) => this.useDefaultComment(e));
			$('.cancel-planned-status').on('click', (e) => this.handleDeletePlanned(e));
		}

		private showEditForm(e: JQuery.ClickEvent, isPlanned: boolean): void {
			const $button = $(e.currentTarget);
			const $displayView = isPlanned ? $('.planned-display-view') : $('.status-display-view');
			const $editForm = isPlanned ? $('.planned-edit-form') : $('.status-edit-form');
			
			$displayView.addClass('hidden');
			$editForm.removeClass('hidden');
			
			const statusSelect = isPlanned ? '#planned_status' : '#current_status';
			this.updateDefaultCommentForSelect(statusSelect);
		}

		private hideEditForm(e: JQuery.ClickEvent): void {
			const $button = $(e.currentTarget);
			const isPlanned = $button.hasClass('cancel-edit-planned');
			
			if (isPlanned) {
				const $editForm = $('.planned-edit-form');
				$editForm.addClass('hidden');
				
				const $displayView = $('.planned-display-view');
				if ($displayView.length) {
					$displayView.removeClass('hidden');
				} else {
					$('.add-planned-status').closest('p').removeClass('hidden');
				}
			} else {
				const $displayView = $('.status-display-view');
				const $editForm = $('.status-edit-form');
				
				$editForm.addClass('hidden');
				$displayView.removeClass('hidden');
			}
		}

		private updateDefaultComment(e: JQuery.ChangeEvent): void {
			const selectId = $(e.currentTarget).attr('id');
			this.updateDefaultCommentForSelect('#' + selectId);
		}

		private updateDefaultCommentForSelect(selector: string): void {
			const $select = $(selector);
			const selectedOption = $select.find('option:selected');
			const defaultComment = selectedOption.data('default-comment') || '';
			const statusValue = selectedOption.val();
			
			const isPlanned = selector.includes('planned');
			const $defaultText = isPlanned ? $('#planned-default-comment-text') : $('#default-comment-text');
			const $table = $select.closest('table');
			const $defaultCommentRow = $table.find('.default-comment-preview-tr');
			
			$defaultText.text(defaultComment);
			
			$table.removeClass('status-0 status-1 status-2').addClass('status-' + statusValue);
			
			if (defaultComment === '') {
				$defaultCommentRow.addClass('hidden');
			} else {
				$defaultCommentRow.removeClass('hidden');
			}
		}

		private useDefaultComment(e: JQuery.ClickEvent): void {
			const $button = $(e.currentTarget);
			const isPlanned = $button.hasClass('use-planned-default-comment');
			const $defaultText = isPlanned ? $('#planned-default-comment-text') : $('#default-comment-text');
			const $textarea = isPlanned ? $('#planned_comment') : $('#current_comment');
			
			let defaultComment = $defaultText.text();
			
			if (defaultComment.includes('[date]')) {
				let dateToUse: string;
				
				if (isPlanned) {
					dateToUse = $('#planned_date').val() as string || '';
				} else {
					const today = new Date();
					dateToUse = today.toLocaleDateString('de-DE', {
						day: '2-digit',
						month: '2-digit',
						year: 'numeric'
					});
				}
				
				defaultComment = defaultComment.replace(/\[date\]/g, dateToUse);
			}
			
			$textarea.val(defaultComment);
		}

		private handleSave(e: JQuery.ClickEvent): void {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const isPlanned = $button.hasClass('save-planned-status');
			const $form = $button.closest('form');
			
			const formData = {
				status: isPlanned ? $('#planned_status').val() : $('#current_status').val(),
				comment: isPlanned ? $('#planned_comment').val() : $('#current_comment').val()
			};
			
			if (isPlanned) {
				(formData as any).date = $('#planned_date').val();
				(formData as any).announce = $('#planned_announce').is(':checked') ? 1 : 0;
			}
			
			$button.prop('disabled', true).text('Speichern...');
			
			$.ajax({
				url: (window as any).isFahreSettingsVars.save_url,
				method: 'POST',
				data: formData,
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', (window as any).isFahreSettingsVars.nonce);
				},
				success: (response) => {
					location.reload();
				},
				error: (xhr, status, error) => {
					alert('Fehler beim Speichern: ' + error);
					$button.prop('disabled', false).text('Speichern');
				}
			});
		}

		private handleDeletePlanned(e: JQuery.ClickEvent): void {
			e.preventDefault();
			
			if (!confirm((window as any).isFahreSettingsVars.l18n.delete_confirm)) {
				return;
			}
			
			const $button = $(e.currentTarget);
			$button.prop('disabled', true).text((window as any).isFahreSettingsVars.l18n.deleting);
			
			$.ajax({
				url: (window as any).isFahreSettingsVars.delete_planned_url,
				method: 'DELETE',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', (window as any).isFahreSettingsVars.nonce);
				},
				success: (response) => {
					location.reload();
				},
				error: (xhr, status, error) => {
					alert((window as any).isFahreSettingsVars.l18n.delete_error + ' ' + error);
					$button.prop('disabled', false).text((window as any).isFahreSettingsVars.l18n.delete);
				}
			});
		}

		private initDatePicker(): void {
			if (typeof (window as any).daterangepicker !== 'undefined' && this.$dateFieldPlanned.length) {
				const tomorrow = new Date();
				tomorrow.setDate(tomorrow.getDate() + 1);
				
				(this.$dateFieldPlanned as any).daterangepicker({
					singleDatePicker: true,
					showDropdowns: true,
					minDate: tomorrow,
					locale: (window as any).isFahreSettingsVars.daterangepicker.locale
				});
			}
		}
	}

	$(document).ready(() => {
		if ($('#faehrebelgern_settings').length) {
			new FaehreBelgernSettings();
		}
	});
})(jQuery);