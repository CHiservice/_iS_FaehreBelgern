(function ($){
	class FaehreStatusLoader {
		private $menuElement!: JQuery;
		private $targetRow!: JQuery;
		
		constructor() {
			this.init();
		}
		
		private init(): void {
			this.$menuElement = $('#is_fb_menu');
			this.$targetRow = this.$menuElement.closest('.et_pb_row');
			
			if (this.$menuElement.length && this.$targetRow.length) {
				this.loadStatusButton();
			}
		}
		
		private loadStatusButton(): void {
			$.get((window as any).isFahreStatusVars.button_url)
				.done((response: any) => {
					if (response && response.button) {
						this.insertButtonIntoFirstColumn(response.button);
					}
				}
			);
		}

		private insertButtonIntoFirstColumn(buttonHtml: string): void {
			const $container = this.$targetRow.find('.et_pb_menu_inner_container').first();

			if ($container.length) {
				$container.append(buttonHtml);
			}
		}
	}
	
	$(document).ready(() => {
		if ($('.et_pb_row#is_fb_menu').length) {
			new FaehreStatusLoader();
		}
	});
})(jQuery);