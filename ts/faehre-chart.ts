import { Chart, registerables } from 'chart.js';
import moment from 'moment';
import 'chartjs-adapter-moment';

(function ($){
	Chart.register(...registerables);

	class FaehreChart {
		private $container!: JQuery;
		private $canvas!: JQuery;
		private chart: Chart | null = null;
		private chartUrl: string;

		constructor() {
			this.chartUrl = (window as any).isFahreChartVars?.chart_url || '';
			this.init();
		}

		private init(): void {
			this.$container = $('#faehre-chart');

			if (this.$container.length === 0) {
				return;
			}

			if (!this.chartUrl) {
				return;
			}

			this.createCanvas();
			this.loadChartData();
		}

		private createCanvas(): void {
			this.$canvas = $('<canvas>')
				.attr('id', 'water-level-chart')
				.attr('width', '800')
				.attr('height', '400')
				.css({
					'width': '100%',
					'height': '400px',
					'border': '1px solid #ccc',
					'background-color': '#f9f9f9',
					'display': 'block'
				});
			
			this.$container.append(this.$canvas);
		}

		private loadChartData(): void {
			$.ajax({
				url: this.chartUrl,
				method: 'GET',
				dataType: 'json'
			})
			.done((response: any) => {
				if (response && response.data) {
					this.createChart(response.data);
				} else {
					this.showError('Invalid chart data received');
				}
			})
			.fail((xhr: any, status: string, error: string) => {
				this.showError('Failed to load chart data');
			});
		}

		private createChart(config: any): void {
			if (!this.$canvas || this.$canvas.length === 0) return;

			const canvas = this.$canvas[0] as HTMLCanvasElement;
			const ctx = canvas.getContext('2d');
			if (!ctx) {
				return;
			}

			if (this.chart) {
				this.chart.destroy();
			}
			
			this.chart = new Chart(ctx, config);
			
			setTimeout(() => {
				if (this.chart) {
					this.chart.resize();
				}
			}, 100);
		}

		private showError(message: string): void {
			const errorHtml = `
				<div class="chart-error" style="
					padding: 20px;
					text-align: center;
					color: #721c24;
					background-color: #f8d7da;
					border: 1px solid #f5c6cb;
					border-radius: 4px;
				">
					<strong>Chart Error:</strong> ${message}
				</div>
			`;
			
			this.$container.html(errorHtml);
		}

		public refresh(): void {
			this.loadChartData();
		}

		public destroy(): void {
			if (this.chart) {
				this.chart.destroy();
				this.chart = null;
			}

			if (this.$canvas) {
				this.$canvas.remove();
			}
		}
	}

	$(document).ready(() => {
		if ($('#faehre-chart').length) {
			new FaehreChart();
		}
	});
})(jQuery);
