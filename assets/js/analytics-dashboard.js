/**
 * Analytics Dashboard JavaScript
 * Handles all frontend interactions for the analytics dashboard
 */

(function($) {
    'use strict';

    class MPWEMAnalyticsDashboard {
        constructor() {
            this.chart = null;
            this.pieCharts = {};
            this.currentFilters = {
                dateFrom: '',
                dateTo: '',
                category: '',
                event: '',
                period: 'daily',
                chartType: 'revenue'
            };
            this.init();
        }

        init() {
            this.bindEvents();
            this.loadInitialData();
            this.initChart();
            this.initPieCharts();
        }

        bindEvents() {
            // Filter events
            $('#mpwem-apply-filters').on('click', () => this.applyFilters());
            
            // Chart control events
            $('#mpwem-chart-period, #mpwem-chart-type').on('change', () => this.updateChart());
            
            // Export events
            $('#mpwem-export-btn').on('click', () => this.showExportModal());
            $('#mpwem-export-confirm').on('click', () => this.exportData());
            

            
            // Modal events
            $('.mpwem-modal-close').on('click', () => this.hideExportModal());
            $(document).on('click', '.mpwem-modal', (e) => {
                if (e.target === e.currentTarget) {
                    this.hideExportModal();
                }
            });
            
            // Refresh button
            $('#mpwem-refresh-btn').on('click', () => this.refreshData());
            
            // Search events
            $('#mpwem-event-search').on('input', () => this.filterEventsTable());
            $('#mpwem-category-search').on('input', () => this.filterCategoriesTable());
            
            // Date range validation
            $('#mpwem-date-from, #mpwem-date-to').on('change', () => this.validateDateRange());
        }



        validateDateRange() {
            const dateFrom = new Date($('#mpwem-date-from').val());
            const dateTo = new Date($('#mpwem-date-to').val());
            
            if (dateFrom > dateTo) {
                $('#mpwem-date-to').val($('#mpwem-date-from').val());
                this.showNotification('Date range corrected: End date cannot be before start date', 'warning');
            }
        }

        applyFilters() {
            this.currentFilters = {
                dateFrom: $('#mpwem-date-from').val(),
                dateTo: $('#mpwem-date-to').val(),
                category: $('#mpwem-category-filter').val(),
                event: $('#mpwem-event-filter').val(),
                period: $('#mpwem-chart-period').val(),
                chartType: $('#mpwem-chart-type').val()
            };
            

            this.loadAnalyticsData();
        }

        loadInitialData() {
            this.showLoading();
            this.loadAnalyticsData();
        }

        loadAnalyticsData() {
            this.showLoading();
            

            
            if (!window.mpwem_analytics) {
                console.error('mpwem_analytics object is not defined!');
                this.hideLoading();
                this.showNotification('Analytics configuration not found', 'error');
                return;
            }
            
            const data = {
                action: 'mpwem_get_analytics_data',
                nonce: window.mpwem_analytics.nonce,
                filters: this.currentFilters
            };



            $.ajax({
                url: window.mpwem_analytics.ajax_url,
                type: 'POST',
                data: data,
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.updateSummaryCards(response.data.summary);
                        this.updateChart(response.data.chart);
                        this.updateEventTable(response.data.events);
                        this.updateCategoryTable(response.data.categories);
                        
                        // Update pie charts
                        this.updatePieCharts();
                    } else {
                        console.error('AJAX request failed:', response);
                        this.showNotification(response.data || window.mpwem_analytics.translations.error, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', xhr, status, error);
                    console.error('XHR status:', xhr.status);
                    console.error('XHR readyState:', xhr.readyState);
                    console.error('XHR responseText:', xhr.responseText);
                    this.hideLoading();
                    this.showNotification(window.mpwem_analytics.translations.error, 'error');
                }
            });
        }

        updateSummaryCards(summary) {
            const currencySymbol = window.mpwem_analytics.currency_symbol;
            
            // Update values
            $('#total-revenue').text(currencySymbol + this.formatNumber(summary.totalRevenue));
            $('#total-orders').text(this.formatNumber(summary.totalOrders));
            $('#total-tickets').text(this.formatNumber(summary.totalTickets));
            $('#average-order').text(currencySymbol + this.formatNumber(summary.averageOrder));
            $('#total-refunds').text(currencySymbol + this.formatNumber(summary.totalRefunds));
            $('#net-revenue').text(currencySymbol + this.formatNumber(summary.netRevenue));
            
            // Update change indicators
            this.updateChangeIndicator('#revenue-change', summary.revenueChange);
            this.updateChangeIndicator('#orders-change', summary.ordersChange);
            this.updateChangeIndicator('#tickets-change', summary.ticketsChange);
            this.updateChangeIndicator('#average-change', summary.averageChange);
            this.updateChangeIndicator('#refunds-change', summary.refundsChange);
            this.updateChangeIndicator('#profit-change', summary.profitChange);
            
            // Add animation
            $('.mpwem-summary-card').addClass('mpwem-fade-in');
        }

        updateChangeIndicator(selector, change) {
            const $element = $(selector);
            const isPositive = change > 0;
            const isNegative = change < 0;
            

            
            $element.removeClass('positive negative neutral');
            
            if (isPositive) {
                $element.addClass('positive').html(`<i class="dashicons dashicons-arrow-up-alt"></i> +${change.toFixed(1)}%`);
            } else if (isNegative) {
                $element.addClass('negative').html(`<i class="dashicons dashicons-arrow-down-alt"></i> ${change.toFixed(1)}%`);
            } else {
                $element.addClass('neutral').html(`<i class="dashicons dashicons-minus"></i> 0%`);
            }
        }

        initChart() {
            const ctx = document.getElementById('mpwem-revenue-chart');
            if (!ctx) return;

            // Get the chart instance from Chart.js registry and destroy it
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            // Destroy our tracked chart instance as well
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Revenue',
                        data: [],
                        borderColor: '#007cba',
                        backgroundColor: 'rgba(0, 124, 186, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#007cba',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#007cba',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed.y;
                                    if (this.currentFilters.chartType === 'revenue') {
                                        return `Revenue: ${window.mpwem_analytics.currency_symbol}${this.formatNumber(value)}`;
                                    } else if (this.currentFilters.chartType === 'orders') {
                                        return `Orders: ${this.formatNumber(value)}`;
                                    } else {
                                        return `Tickets: ${this.formatNumber(value)}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#6b7280',
                                callback: (value) => {
                                    if (this.currentFilters.chartType === 'revenue') {
                                        return window.mpwem_analytics.currency_symbol + this.formatNumber(value);
                                    }
                                    return this.formatNumber(value);
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        updateChart(chartData = null) {
            if (!this.chart) return;
            
            this.currentFilters.period = $('#mpwem-chart-period').val();
            this.currentFilters.chartType = $('#mpwem-chart-type').val();
            
            if (chartData) {
                this.chart.data.labels = chartData.labels;
                this.chart.data.datasets[0].data = chartData.data;
                this.chart.data.datasets[0].label = this.getChartLabel();
                this.chart.update('active');
            } else {
                // Reload data with new filters
                this.loadAnalyticsData();
            }
        }

        getChartLabel() {
            const type = this.currentFilters.chartType;
            switch (type) {
                case 'revenue':
                    return 'Revenue';
                case 'orders':
                    return 'Orders';
                case 'tickets':
                    return 'Tickets Sold';
                default:
                    return 'Revenue';
            }
        }

        initPieCharts() {
            this.initPieChart('mpwem-revenue-category-pie', 'Revenue by Category');
            this.initPieChart('mpwem-revenue-event-pie', 'Revenue by Event');
            this.initPieChart('mpwem-tickets-category-pie', 'Tickets by Category');
            this.initPieChart('mpwem-orders-status-pie', 'Orders by Status');
        }

        initPieChart(canvasId, title) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            this.pieCharts[canvasId] = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#007cba',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    
                                    if (canvasId.includes('revenue')) {
                                        return `${label}: ${window.mpwem_analytics.currency_symbol}${this.formatNumber(value)} (${percentage}%)`;
                                    } else if (canvasId.includes('tickets')) {
                                        return `${label}: ${this.formatNumber(value)} tickets (${percentage}%)`;
                                    } else if (canvasId.includes('orders')) {
                                        return `${label}: ${this.formatNumber(value)} orders (${percentage}%)`;
                                    }
                                    return `${label}: ${this.formatNumber(value)} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        updatePieCharts() {
            this.loadPieChartData();
        }

        loadPieChartData() {
            const data = {
                action: 'mpwem_get_pie_chart_data',
                nonce: window.mpwem_analytics.nonce,
                date_from: this.currentFilters.dateFrom,
                date_to: this.currentFilters.dateTo,
                filters: JSON.stringify({
                    category: this.currentFilters.category,
                    event: this.currentFilters.event
                })
            };

            $.ajax({
                url: window.mpwem_analytics.ajax_url,
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success && response.data) {
                        this.updatePieChart('mpwem-revenue-category-pie', response.data.revenue_by_category);
                        this.updatePieChart('mpwem-revenue-event-pie', response.data.revenue_by_event);
                        this.updatePieChart('mpwem-tickets-category-pie', response.data.tickets_by_category);
                        this.updatePieChart('mpwem-orders-status-pie', response.data.orders_by_status);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading pie chart data:', error);
                }
            });
        }

        updatePieChart(canvasId, chartData) {
            if (!this.pieCharts[canvasId]) return;

            const chart = this.pieCharts[canvasId];
            
            // Prepare data for Chart.js
            const labels = chartData.map(item => item.label);
            const values = chartData.map(item => item.value);
            const colors = chartData.map(item => item.color);

            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].backgroundColor = colors;
            
            chart.update('active');
        }

        updateEventTable(events) {
            const tbody = $('#mpwem-events-tbody');
            tbody.empty();
            
            if (!events || events.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #6b7280;">
                            ${window.mpwem_analytics.translations.no_data}
                        </td>
                    </tr>
                `);
                return;
            }
            
            events.forEach(event => {
                const row = `
                    <tr data-event-name="${event.name.toLowerCase()}">
                        <td>
                            <strong>${event.name}</strong>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                ${event.date}
                            </div>
                        </td>
                        <td>
                            <span class="mpwem-text-success">
                                ${window.mpwem_analytics.currency_symbol}${this.formatNumber(event.revenue)}
                            </span>
                        </td>
                        <td>${this.formatNumber(event.tickets)}</td>
                        <td>${this.formatNumber(event.orders)}</td>
                        <td>
                            ${window.mpwem_analytics.currency_symbol}${this.formatNumber(event.averageOrder)}
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            tbody.addClass('mpwem-slide-up');
        }

        updateCategoryTable(categories) {
            const tbody = $('#mpwem-categories-tbody');
            tbody.empty();
            
            if (!categories || categories.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #6b7280;">
                            ${window.mpwem_analytics.translations.no_data}
                        </td>
                    </tr>
                `);
                return;
            }
            
            categories.forEach(category => {
                const row = `
                    <tr data-category-name="${category.name.toLowerCase()}">
                        <td>
                            <strong>${category.name}</strong>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                ${category.description || ''}
                            </div>
                        </td>
                        <td>
                            <span class="mpwem-text-success">
                                ${window.mpwem_analytics.currency_symbol}${this.formatNumber(category.revenue)}
                            </span>
                        </td>
                        <td>${this.formatNumber(category.tickets)}</td>
                        <td>${this.formatNumber(category.events)}</td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            tbody.addClass('mpwem-slide-up');
        }

        filterEventsTable() {
            const searchTerm = $('#mpwem-event-search').val().toLowerCase();
            const rows = $('#mpwem-events-tbody tr');
            
            rows.each(function() {
                const eventName = $(this).data('event-name') || '';
                const isVisible = eventName.includes(searchTerm);
                $(this).toggle(isVisible);
            });
        }

        filterCategoriesTable() {
            const searchTerm = $('#mpwem-category-search').val().toLowerCase();
            const rows = $('#mpwem-categories-tbody tr');
            
            rows.each(function() {
                const categoryName = $(this).data('category-name') || '';
                const isVisible = categoryName.includes(searchTerm);
                $(this).toggle(isVisible);
            });
        }

        showExportModal() {
            $('#mpwem-export-modal').fadeIn(300);
            $('#export-date-from').val(this.currentFilters.dateFrom);
            $('#export-date-to').val(this.currentFilters.dateTo);
        }

        hideExportModal() {
            $('#mpwem-export-modal').fadeOut(300);
        }

        exportData() {
            const format = $('input[name="export-format"]:checked').val();
            const dateFrom = $('#export-date-from').val();
            const dateTo = $('#export-date-to').val();
            
            const data = {
                action: 'mpwem_export_analytics',
                nonce: window.mpwem_analytics.nonce,
                format: format,
                dateFrom: dateFrom,
                dateTo: dateTo,
                filters: this.currentFilters
            };
            
            // Create a temporary form to download the file
            const form = $('<form>', {
                method: 'POST',
                action: window.mpwem_analytics.ajax_url,
                style: 'display: none;'
            });
            
            Object.keys(data).forEach(key => {
                if (typeof data[key] === 'object') {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: key,
                        value: JSON.stringify(data[key])
                    }));
                } else {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: key,
                        value: data[key]
                    }));
                }
            });
            
            $('body').append(form);
            form.submit();
            form.remove();
            
            this.hideExportModal();
            this.showNotification(window.mpwem_analytics.translations.export_success, 'success');
        }

        refreshData() {
            this.loadAnalyticsData();
            this.showNotification('Data refreshed successfully', 'success');
        }

        showLoading() {
            $('#mpwem-loading').fadeIn(300);
            $('#mpwem-summary-cards, .mpwem-analytics-charts, .mpwem-analytics-pie-charts, .mpwem-analytics-tables').css('opacity', '0.5');
        }

        hideLoading() {
            $('#mpwem-loading').fadeOut(300);
            $('#mpwem-summary-cards, .mpwem-analytics-charts, .mpwem-analytics-pie-charts, .mpwem-analytics-tables').css('opacity', '1');
        }

        showNotification(message, type = 'info') {
            // Remove existing notifications
            $('.mpwem-notification').remove();
            
            const notification = $(`
                <div class="mpwem-notification mpwem-notification-${type}">
                    <div class="mpwem-notification-content">
                        <span class="mpwem-notification-message">${message}</span>
                        <button class="mpwem-notification-close">&times;</button>
                    </div>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.fadeOut(300, () => notification.remove());
            }, 5000);
            
            // Manual close
            notification.find('.mpwem-notification-close').on('click', () => {
                notification.fadeOut(300, () => notification.remove());
            });
        }

        formatNumber(num) {
            if (num === null || num === undefined) return '0';
            
            const number = parseFloat(num);
            if (isNaN(number)) return '0';
            
            if (number >= 1000000) {
                return (number / 1000000).toFixed(1) + 'M';
            } else if (number >= 1000) {
                return (number / 1000).toFixed(1) + 'K';
            } else {
                return number.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is required for the analytics dashboard');
            return;
        }
        
        // Check if localization object exists
        if (typeof window.mpwem_analytics === 'undefined') {
            console.error('Analytics localization data is missing');
            return;
        }
        
        // Ensure only one instance is created
        if (typeof window.mpwemAnalyticsDashboard === 'undefined') {
            window.mpwemAnalyticsDashboard = new MPWEMAnalyticsDashboard();
        }
    });

})(jQuery);

// Add notification styles dynamically
jQuery(document).ready(function($) {
    if (!$('#mpwem-notification-styles').length) {
        $('head').append(`
            <style id="mpwem-notification-styles">
                .mpwem-notification {
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    z-index: 999999;
                    max-width: 400px;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    animation: slideInRight 0.3s ease-out;
                }
                
                .mpwem-notification-success {
                    background: #10b981;
                    color: white;
                }
                
                .mpwem-notification-error {
                    background: #ef4444;
                    color: white;
                }
                
                .mpwem-notification-warning {
                    background: #f59e0b;
                    color: white;
                }
                
                .mpwem-notification-info {
                    background: #3b82f6;
                    color: white;
                }
                
                .mpwem-notification-content {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 15px 20px;
                }
                
                .mpwem-notification-message {
                    font-weight: 500;
                }
                
                .mpwem-notification-close {
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 18px;
                    cursor: pointer;
                    padding: 0;
                    margin-left: 15px;
                    opacity: 0.8;
                    transition: opacity 0.3s ease;
                }
                
                .mpwem-notification-close:hover {
                    opacity: 1;
                }
                
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            </style>
        `);
    }
});