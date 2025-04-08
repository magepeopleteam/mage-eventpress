/**
 * Event Analytics Dashboard JavaScript
 */
(function($) {
    'use strict';

    // Chart instances
    let salesChart, eventsChart, ticketTypesChart, weekdayChart;

    // Initialize the dashboard
    function initDashboard() {
        // Set up event listeners
        setupEventListeners();

        // Initialize charts
        initCharts();

        // Load initial data
        loadAnalyticsData();
    }

    // Set up event listeners
    function setupEventListeners() {
        // Date range selector
        $('#mep-date-range').on('change', function() {
            const value = $(this).val();

            if (value === 'custom') {
                $('#mep-custom-date-range').show();
            } else {
                $('#mep-custom-date-range').hide();

                // Set date range based on selection
                const endDate = new Date();
                let startDate = new Date();

                startDate.setDate(endDate.getDate() - parseInt(value));

                $('#mep-start-date').val(formatDate(startDate));
                $('#mep-end-date').val(formatDate(endDate));
            }
        });

        // Apply filters button
        $('#mep-apply-filters').on('click', function() {
            loadAnalyticsData();
        });

        // Export to CSV button
        $('#mep-export-csv').on('click', function() {
            exportToCsv();
        });
    }

    // Initialize charts
    function initCharts() {
        // Sales over time chart
        const salesChartCtx = document.getElementById('mep-sales-chart').getContext('2d');
        salesChart = new Chart(salesChartCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Sales',
                    backgroundColor: 'rgba(241, 41, 113, 0.2)',
                    borderColor: 'rgba(241, 41, 113, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(241, 41, 113, 1)',
                    data: []
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'MMM d, yyyy',
                            displayFormats: {
                                day: 'MMM d'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Amount'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });

        // Tickets sold by event chart
        const eventsChartCtx = document.getElementById('mep-events-chart').getContext('2d');
        eventsChart = new Chart(eventsChartCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Tickets Sold',
                    backgroundColor: 'rgba(50, 193, 164, 0.7)',
                    borderColor: 'rgba(50, 193, 164, 1)',
                    borderWidth: 1,
                    data: []
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Tickets Sold'
                        }
                    }
                }
            }
        });

        // Ticket types distribution chart
        const ticketTypesChartCtx = document.getElementById('mep-ticket-types-chart').getContext('2d');
        ticketTypesChart = new Chart(ticketTypesChartCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        'rgba(241, 41, 113, 0.7)',
                        'rgba(50, 193, 164, 0.7)',
                        'rgba(255, 190, 0, 0.7)',
                        'rgba(144, 19, 254, 0.7)',
                        'rgba(237, 90, 84, 0.7)',
                        'rgba(48, 48, 48, 0.7)'
                    ],
                    borderColor: [
                        'rgba(241, 41, 113, 1)',
                        'rgba(50, 193, 164, 1)',
                        'rgba(255, 190, 0, 1)',
                        'rgba(144, 19, 254, 1)',
                        'rgba(237, 90, 84, 1)',
                        'rgba(48, 48, 48, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Sales by day of week chart
        const weekdayChartCtx = document.getElementById('mep-weekday-chart').getContext('2d');
        weekdayChart = new Chart(weekdayChartCtx, {
            type: 'bar',
            data: {
                labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                datasets: [{
                    label: 'Sales Amount',
                    backgroundColor: 'rgba(255, 190, 0, 0.7)',
                    borderColor: 'rgba(255, 190, 0, 1)',
                    borderWidth: 1,
                    data: []
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Amount'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    }

    // Load analytics data from the server
    function loadAnalyticsData() {
        // Show loading state
        showLoading();

        // Get filter values
        const startDate = $('#mep-start-date').val();
        const endDate = $('#mep-end-date').val();
        const eventId = $('#mep-event-filter').val();

        // Make AJAX request
        $.ajax({
            url: mep_analytics_data.ajax_url,
            type: 'POST',
            data: {
                action: 'mep_get_analytics_data',
                nonce: mep_analytics_data.nonce,
                start_date: startDate,
                end_date: endDate,
                event_id: eventId
            },
            success: function(response) {
                if (response.success) {
                    updateDashboard(response.data);
                } else {
                    alert('Error loading analytics data. Please try again.');
                }

                // Hide loading state
                hideLoading();
            },
            error: function() {
                alert('Error loading analytics data. Please try again.');

                // Hide loading state
                hideLoading();
            }
        });
    }

    // Update dashboard with new data
    function updateDashboard(data) {
        // Update summary cards
        updateSummaryCards(data.summary);

        // Update charts
        updateCharts(data);

        // Update data table
        updateDataTable(data.detailed_data, data.debug);
    }

    // Update summary cards
    function updateSummaryCards(summary) {
        $('#mep-total-sales .mep-card-value').text(formatCurrency(summary.total_sales));
        $('#mep-tickets-sold .mep-card-value').text(summary.tickets_sold);
        $('#mep-total-events .mep-card-value').text(summary.total_events);
        $('#mep-avg-ticket-price .mep-card-value').text(formatCurrency(summary.avg_ticket_price));
    }

    // Update charts
    function updateCharts(data) {
        // Update sales chart
        salesChart.data.datasets[0].data = data.sales_chart;
        salesChart.update();

        // Update events chart
        eventsChart.data.labels = data.events_chart.labels;
        eventsChart.data.datasets[0].data = data.events_chart.data;
        eventsChart.update();

        // Update ticket types chart
        ticketTypesChart.data.labels = data.ticket_types_chart.labels;
        ticketTypesChart.data.datasets[0].data = data.ticket_types_chart.data;
        ticketTypesChart.update();

        // Update weekday chart
        weekdayChart.data.datasets[0].data = data.weekday_chart.data;
        weekdayChart.update();
    }

    // Update data table
    function updateDataTable(detailedData, debug) {
        const tableBody = $('#mep-data-table-body');
        tableBody.empty();

        if (detailedData.length === 0) {
            let message = 'No data available for the selected filters.';

            // Add debug information if available
            if (debug) {
                message += '<br><br><strong>Debug Information:</strong><br>';
                message += 'Events found: ' + debug.events_count + '<br>';
                message += 'Attendees found: ' + (debug.attendees_found ? 'Yes' : 'No') + '<br>';
                message += 'Total attendees: ' + debug.attendee_count + '<br>';
                message += 'Date range: ' + debug.date_range.start + ' to ' + debug.date_range.end + '<br>';
                message += 'Order statuses checked: ' + Object.values(debug.order_statuses).join(', ') + '<br>';
                message += 'Including all order statuses: ' + (debug.include_all_statuses ? 'Yes' : 'No') + '<br>';

                if (debug.event_ids && debug.event_ids.length > 0) {
                    message += 'Event IDs: ' + debug.event_ids.join(', ') + '<br>';
                }

                if (debug.all_attendees_count) {
                    message += '<br><strong>All Attendees in Database:</strong><br>';
                    for (const [status, count] of Object.entries(debug.all_attendees_count)) {
                        message += status + ': ' + count + '<br>';
                    }
                    message += 'Total attendees in database: ' + debug.total_attendees_in_db + '<br>';
                }

                if (debug.sample_attendee_from_db) {
                    message += '<br><strong>Sample Attendee from Database:</strong><br>';
                    message += 'ID: ' + debug.sample_attendee_from_db.id + '<br>';
                    message += 'Order Status: ' + debug.sample_attendee_from_db.order_status + '<br>';
                    message += 'Event ID: ' + debug.sample_attendee_from_db.event_id + '<br>';
                    message += 'Order ID: ' + debug.sample_attendee_from_db.order_id + '<br>';
                }

                if (debug.sample_attendee) {
                    message += '<br><strong>Sample Attendee:</strong><br>';
                    message += 'ID: ' + debug.sample_attendee.id + '<br>';
                    message += 'Order Status: ' + debug.sample_attendee.order_status + '<br>';
                    message += 'Event ID: ' + debug.sample_attendee.event_id + '<br>';
                    message += 'Order ID: ' + debug.sample_attendee.order_id + '<br>';
                }
            }

            tableBody.append('<tr><td colspan="6">' + message + '</td></tr>');
            return;
        }

        // Sort by date (newest first)
        detailedData.sort((a, b) => new Date(b.date) - new Date(a.date));

        // Add rows to table
        detailedData.forEach(function(item) {
            tableBody.append(`
                <tr>
                    <td>${item.event}</td>
                    <td>${formatDate(new Date(item.date))}</td>
                    <td>${item.tickets_sold}</td>
                    <td>${formatCurrency(item.total_sales)}</td>
                    <td>${item.available_seats}</td>
                    <td>${item.occupancy_rate}%</td>
                </tr>
            `);
        });
    }

    // Export data to CSV
    function exportToCsv() {
        // Show loading state
        showLoading();

        // Get filter values
        const startDate = $('#mep-start-date').val();
        const endDate = $('#mep-end-date').val();
        const eventId = $('#mep-event-filter').val();

        // Make AJAX request
        $.ajax({
            url: mep_analytics_data.ajax_url,
            type: 'POST',
            data: {
                action: 'mep_export_analytics_csv',
                nonce: mep_analytics_data.nonce,
                start_date: startDate,
                end_date: endDate,
                event_id: eventId
            },
            success: function(response) {
                if (response.success) {
                    // Create and download CSV file
                    const blob = new Blob([response.data.csv_data], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');

                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', response.data.filename);
                    link.style.visibility = 'hidden';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error exporting data. Please try again.');
                }

                // Hide loading state
                hideLoading();
            },
            error: function() {
                alert('Error exporting data. Please try again.');

                // Hide loading state
                hideLoading();
            }
        });
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        const currencySymbol = mep_analytics_data.currency_symbol || '$';
        return currencySymbol + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Helper function to format date
    function formatDate(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Show loading state
    function showLoading() {
        // Add loading class to dashboard
        $('.mep-analytics-dashboard').addClass('loading');

        // Disable filter buttons
        $('#mep-apply-filters, #mep-export-csv').prop('disabled', true);
    }

    // Hide loading state
    function hideLoading() {
        // Remove loading class from dashboard
        $('.mep-analytics-dashboard').removeClass('loading');

        // Enable filter buttons
        $('#mep-apply-filters, #mep-export-csv').prop('disabled', false);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initDashboard();
    });

})(jQuery);
