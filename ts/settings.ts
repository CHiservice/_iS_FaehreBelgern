(function ($){

	interface FaehreBelgernSettingsConfig {
		select?: string;
		textarea?: string;
		defaultTextTr?: string;
		defaultText?: string;
		button?: string;
		typeSelect?: string;
		dateField?: string;
		selectPlanned?: string;
		textareaPlanned?: string;
		defaultTextTrPlanned?: string;
		defaultTextPlanned?: string;
		buttonPlanned?: string;
		dateFieldPlanned?: string;
	}

	class FaehreBelgernSettings {
		private $select!: JQuery<HTMLSelectElement>;
		private $textarea!: JQuery<HTMLTextAreaElement>;
		private $defaultTextTr!: JQuery<HTMLElement>;
		private $defaultText!: JQuery<HTMLElement>;
		private $button!: JQuery<HTMLButtonElement>;
		private $typeSelect!: JQuery<HTMLSelectElement>;
		private $dateField!: JQuery<HTMLInputElement>;
		private $selectPlanned!: JQuery<HTMLSelectElement>;
		private $textareaPlanned!: JQuery<HTMLTextAreaElement>;
		private $defaultTextTrPlanned!: JQuery<HTMLElement>;
		private $defaultTextPlanned!: JQuery<HTMLElement>;
		private $buttonPlanned!: JQuery<HTMLButtonElement>;
		private $dateFieldPlanned!: JQuery<HTMLInputElement>;
		private config: Required<FaehreBelgernSettingsConfig>;
		private originalStatus!: string;
		private originalComment!: string;

		constructor(config: FaehreBelgernSettingsConfig = {}) {
			this.config = {
				select: '#is_faehrebelgern_settings_status',
				textarea: '#is_faehrebelgern_settings_comment',
				defaultTextTr: '.default_text_tr',
				defaultText: '.default_text',
				button: '.default_text_wrapper button',
				typeSelect: '#is_faehrebelgern_settings_type',
				dateField: '#is_faehrebelgern_settings_date',
				selectPlanned: '#is_faehrebelgern_settings_status_planned',
				textareaPlanned: '#is_faehrebelgern_settings_comment_planned',
				defaultTextTrPlanned: '.default_text_tr_planned',
				defaultTextPlanned: '.default_text_planned',
				buttonPlanned: '.default_text_wrapper button.planned',
				dateFieldPlanned: '#is_faehrebelgern_settings_date_planned',
				...config
			};

			this.init();
		}

		private init(): void {
			this.$select = $(this.config.select);
			this.$textarea = $(this.config.textarea);
			this.$defaultTextTr = $(this.config.defaultTextTr);
			this.$defaultText = $(this.config.defaultText);
			this.$button = $(this.config.button);
			this.$typeSelect = $(this.config.typeSelect);
			this.$dateField = $(this.config.dateField);
			this.$selectPlanned = $(this.config.selectPlanned);
			this.$textareaPlanned = $(this.config.textareaPlanned);
			this.$defaultTextTrPlanned = $(this.config.defaultTextTrPlanned);
			this.$defaultTextPlanned = $(this.config.defaultTextPlanned);
			this.$buttonPlanned = $(this.config.buttonPlanned);
			this.$dateFieldPlanned = $(this.config.dateFieldPlanned);

			if (!this.$select.length || !this.$textarea.length || !this.$typeSelect.length) {
				return;
			}

			this.setupEventListeners();
			this.storeOriginalValues();
			this.updateDefaultText();
			this.updateDisplayMode();
			this.initDatePicker();
		}

		private setupEventListeners(): void {
			this.$select.on('change', () => {
				this.updateDefaultText();
			});

			this.$selectPlanned.on('change', () => {
				this.updatePlannedDefaultText();
			});

			this.$typeSelect.on('change', () => {
				this.updateDisplayMode();
			});

			this.$button.on('click', (e) => {
				e.preventDefault();
				this.applyDefaultText();
			});

			this.$buttonPlanned.on('click', (e) => {
				e.preventDefault();
				this.applyPlannedDefaultText();
			});
		}

	private updateDefaultText(): void {
		const defaultText = this.$select.find('option:selected').data('default-text') || '';
		const selectedValue = this.$select.val();
		
		this.$defaultText.text(defaultText);
		this.$defaultText.removeClass('status-0 status-1 status-2');
		this.$textarea.removeClass('status-0 status-1 status-2');
		if (defaultText.trim() == '') {
			this.$defaultTextTr.addClass('hidden');
		} else {
			this.$defaultTextTr.removeClass('hidden');
		}

		this.$defaultText.addClass(`status-${selectedValue}`);
		this.$textarea.addClass(`status-${selectedValue}`);
	}

	private applyDefaultText(): void {
		const defaultText = this.getDefaultText();
		if (defaultText.trim()) {
			this.$textarea.val(defaultText);
			this.$textarea.trigger('change');
		}
	}

	private getDefaultText(): string {
		return this.$select.find('option:selected').data('default-text') || '';
	}

	private updateDisplayMode(): void {
		const typeValue = this.$typeSelect.val();
		
		this.restoreOriginalValues();
		
		if (typeValue === 'date') {
			$('#immediately-settings').addClass('hidden');
			$('#planned-settings').removeClass('hidden');
		} else {
			$('#immediately-settings').removeClass('hidden');
			$('#planned-settings').addClass('hidden');
		}
	}

	private storeOriginalValues(): void {
		this.originalStatus = this.$select.val() as string;
		this.originalComment = this.$textarea.val() as string;
	}

	private restoreOriginalValues(): void {
		this.$select.val(this.originalStatus).trigger('change');
		this.$textarea.val(this.originalComment);
	}

	private updatePlannedDefaultText(): void {
		const defaultText = this.$selectPlanned.find('option:selected').data('default-text') || '';
		const selectedValue = this.$selectPlanned.val();
		
		this.$defaultTextPlanned.text(defaultText);
		this.$defaultTextPlanned.removeClass('status-0 status-1 status-2');
		this.$textareaPlanned.removeClass('status-0 status-1 status-2');
		if (defaultText.trim() == '') {
			this.$defaultTextTrPlanned.addClass('hidden');
		} else {
			this.$defaultTextTrPlanned.removeClass('hidden');
		}

		this.$defaultTextPlanned.addClass(`status-${selectedValue}`);
		this.$textareaPlanned.addClass(`status-${selectedValue}`);
	}

	private applyPlannedDefaultText(): void {
		const defaultText = this.getPlannedDefaultText();
		if (defaultText.trim()) {
			this.$textareaPlanned.val(defaultText);
			this.$textareaPlanned.trigger('change');
		}
	}

	private getPlannedDefaultText(): string {
		return this.$selectPlanned.find('option:selected').data('default-text') || '';
	}

	private initDatePicker(): void {
		if (typeof (window as any).daterangepicker !== 'undefined' && this.$dateFieldPlanned.length) {
			const tomorrow = new Date();
			tomorrow.setDate(tomorrow.getDate() + 1);
			
			(this.$dateFieldPlanned as any).daterangepicker({
				singleDatePicker: true,
				showDropdowns: true,
				minDate: tomorrow,
				locale: {
					format: 'DD.MM.YYYY',
					separator: ' - ',
					applyLabel: 'Übernehmen',
					cancelLabel: 'Abbrechen',
					fromLabel: 'Von',
					toLabel: 'Bis',
					customRangeLabel: 'Benutzerdefiniert',
					weekLabel: 'W',
					daysOfWeek: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
					monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
					firstDay: 1
				}
			});
		}
	}

	public refresh(): void {
		this.updateDefaultText();
		this.updateDisplayMode();
	}

	public reset(): void {
		this.updateDefaultText();
		this.updateDisplayMode();
	}

	public getCurrentDefaultText(): string {
		return this.getDefaultText();
	}

	public getSelectedType(): string {
		return this.$typeSelect.val() as string;
	}

	public getSelectedDate(): string {
		return this.$dateField.val() as string;
	}
	}

	$(document).ready(() => {
		if ($('#faehrebelgern_settings').length) {
			new FaehreBelgernSettings();
		}
	});

})(jQuery);