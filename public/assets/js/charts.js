/**
 * Charts.js - Main JavaScript for Fisherfolk Dashboard
 * Calapan City FMO - Powerbyte IT Solutions
 * Uses Chart.js for data visualization
 */

// Configuration
const API_BASE_URL = '/api';
const THEME_COLORS = {
    primary: '#FFA500',    // Blue
    secondary: '#0000FF',  // Orange
    success: '#28a745',
    info: '#17a2b8',
    warning: '#ffc107',
    danger: '#dc3545'
};

// Chart.js default configuration
Chart.defaults.color = '#666';
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

// Global chart instances
let barangayChart, genderChart, ageGroupChart, categoryChart, barangayCategoryChart;

/**
 * Fetch data from API endpoint
 */
async function fetchData(endpoint) {
    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to fetch data');
        }
        
        return data.data;
    } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        return null;
    }
}

/**
 * Update summary statistics cards
 */
async function updateSummaryStats() {
    const stats = await fetchData('summary-stats.php');
    
    console.log('Summary stats received:', stats);
    
    if (stats) {
        document.getElementById('total-fisherfolk').textContent = stats.total_fisherfolk || 0;
        document.getElementById('total-barangays').textContent = stats.barangays || 0;
        document.getElementById('total-male').textContent = stats.male || 0;
        document.getElementById('total-female').textContent = stats.female || 0;
        console.log('Summary stats updated successfully');
    } else {
        console.error('No summary stats data received');
    }
}

/**
 * Create Barangay Distribution Chart (Bar)
 */
async function createBarangayChart() {
    const data = await fetchData('barangay-stats.php');
    
    if (!data || data.length === 0) {
        console.error('No barangay data available');
        return;
    }
    
    const labels = data.map(item => item.barangay);
    const values = data.map(item => parseInt(item.count));
    
    const ctx = document.getElementById('barangayChart').getContext('2d');
    
    if (barangayChart) {
        barangayChart.destroy();
    }
    
    barangayChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Fisherfolk',
                data: values,
                backgroundColor: THEME_COLORS.primary,
                borderColor: THEME_COLORS.primary,
                borderWidth: 1
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
                    callbacks: {
                        label: function(context) {
                            return `Count: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Create Gender Distribution Chart (Pie)
 */
async function createGenderChart() {
    const data = await fetchData('gender-stats.php');
    
    if (!data || data.length === 0) {
        console.error('No gender data available');
        return;
    }
    
    const labels = data.map(item => item.gender);
    const values = data.map(item => parseInt(item.count));
    
    const ctx = document.getElementById('genderChart').getContext('2d');
    
    if (genderChart) {
        genderChart.destroy();
    }
    
    genderChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    THEME_COLORS.primary,
                    THEME_COLORS.secondary
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Create Age Group Distribution Chart (Bar)
 */
async function createAgeGroupChart() {
    const data = await fetchData('age-group-stats.php');
    
    if (!data || data.length === 0) {
        console.error('No age group data available');
        return;
    }
    
    const labels = data.map(item => item.age_group);
    const values = data.map(item => parseInt(item.count));
    
    const ctx = document.getElementById('ageGroupChart').getContext('2d');
    
    if (ageGroupChart) {
        ageGroupChart.destroy();
    }
    
    ageGroupChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Fisherfolk',
                data: values,
                backgroundColor: THEME_COLORS.secondary,
                borderColor: THEME_COLORS.secondary,
                borderWidth: 1
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
                    callbacks: {
                        label: function(context) {
                            return `Count: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Create Category Distribution Chart (Horizontal Bar)
 */
async function createCategoryChart() {
    const data = await fetchData('category-stats.php');
    
    if (!data || data.length === 0) {
        console.error('No category data available');
        return;
    }
    
    const labels = data.map(item => item.category);
    const values = data.map(item => parseInt(item.count));
    
    const colors = [
        THEME_COLORS.primary,
        THEME_COLORS.secondary,
        THEME_COLORS.success,
        THEME_COLORS.info,
        THEME_COLORS.warning,
        THEME_COLORS.danger
    ];
    
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    if (categoryChart) {
        categoryChart.destroy();
    }
    
    categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Fisherfolk',
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Count: ${context.parsed.x}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Load barangay list into filter dropdown
 */
async function loadBarangayFilter() {
    const data = await fetchData('barangay-list.php');
    
    if (!data || data.length === 0) {
        console.error('No barangay data available');
        return;
    }
    
    const select = document.getElementById('barangayFilter');
    
    // Add barangay options
    data.forEach(item => {
        const option = document.createElement('option');
        option.value = item.barangay;
        option.textContent = item.barangay;
        select.appendChild(option);
    });
    
    // Add event listener for filter change
    select.addEventListener('change', function() {
        createBarangayCategoryChart(this.value);
        loadFisherfolkList(this.value);
    });
}

/**
 * Create Barangay Category Distribution Chart (Filtered by Barangay)
 */
async function createBarangayCategoryChart(barangay = 'all') {
    const endpoint = barangay === 'all' 
        ? 'barangay-category-stats.php' 
        : `barangay-category-stats.php?barangay=${encodeURIComponent(barangay)}`;
    
    const data = await fetchData(endpoint);
    
    if (!data || data.length === 0) {
        console.error('No barangay category data available');
        return;
    }
    
    const labels = data.map(item => item.category);
    const values = data.map(item => parseInt(item.count));
    
    // Generate gradient colors
    const colors = [
        THEME_COLORS.primary,
        THEME_COLORS.secondary,
        THEME_COLORS.success,
        THEME_COLORS.info,
        THEME_COLORS.warning,
        THEME_COLORS.danger
    ];
    
    const ctx = document.getElementById('barangayCategoryChart').getContext('2d');
    
    if (barangayCategoryChart) {
        barangayCategoryChart.destroy();
    }
    
    // Update chart title based on filter
    const titleText = barangay === 'all' 
        ? 'All Barangays' 
        : barangay;
    
    barangayCategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: `Fisherfolk in ${titleText}`,
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Count: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Load fisherfolk list for selected barangay
 */
async function loadFisherfolkList(barangay = 'all') {
    const endpoint = barangay === 'all' 
        ? 'barangay-fisherfolk-list.php' 
        : `barangay-fisherfolk-list.php?barangay=${encodeURIComponent(barangay)}`;
    
    const data = await fetchData(endpoint);
    
    const tbody = document.getElementById('fisherfolkTableBody');
    const noDataMsg = document.getElementById('noDataMessage');
    const titleSpan = document.getElementById('fisherfolfListTitle');
    
    // Update title
    if (barangay === 'all') {
        titleSpan.textContent = '(All Barangays)';
    } else {
        titleSpan.textContent = `(${barangay})`;
    }
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '';
        noDataMsg.style.display = 'block';
        return;
    }
    
    noDataMsg.style.display = 'none';
    
    // Build table rows
    tbody.innerHTML = data.map(fisherfolk => {
        // Build activity categories badges
        const categories = [];
        if (fisherfolk.boat_owneroperator == 1) categories.push('<span class="badge bg-primary">Boat Owner/Operator</span>');
        if (fisherfolk.capture_fishing == 1) categories.push('<span class="badge bg-success">Capture Fishing</span>');
        if (fisherfolk.gleaning == 1) categories.push('<span class="badge bg-info">Gleaning</span>');
        if (fisherfolk.vendor == 1) categories.push('<span class="badge bg-warning">Vendor</span>');
        if (fisherfolk.fish_processing == 1) categories.push('<span class="badge bg-danger">Fish Processing</span>');
        if (fisherfolk.aquaculture == 1) categories.push('<span class="badge bg-secondary">Aquaculture</span>');
        
        const categoriesHtml = categories.length > 0 ? categories.join(' ') : '<span class="text-muted">None</span>';
        
        return `
            <tr>
                <td>${fisherfolk.id_number || 'N/A'}</td>
                <td>${fisherfolk.full_name}</td>
                <td>${fisherfolk.address}</td>
                <td>${fisherfolk.sex}</td>
                <td>${fisherfolk.contact_number || 'N/A'}</td>
                <td>${categoriesHtml}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Update last refreshed timestamp
 */
function updateLastRefreshed() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    document.getElementById('last-updated').textContent = timeString;
}

/**
 * Initialize all charts and load data
 */
async function initializeDashboard() {
    console.log('Initializing Fisherfolk Dashboard...');
    
    // Show loading state
    document.body.style.cursor = 'wait';
    
    try {
        // Load all data and create charts
        await Promise.all([
            updateSummaryStats(),
            createBarangayChart(),
            createGenderChart(),
            createAgeGroupChart(),
            createCategoryChart(),
            loadBarangayFilter()
        ]);
        
        // Create the filtered barangay category chart
        await createBarangayCategoryChart('all');
        
        // Load fisherfolk list
        await loadFisherfolkList('all');
        
        // Update timestamp
        updateLastRefreshed();
        
        console.log('Dashboard initialized successfully');
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        alert('Failed to load dashboard data. Please check your database connection.');
    } finally {
        document.body.style.cursor = 'default';
    }
}

/**
 * Refresh all charts
 */
async function refreshDashboard() {
    console.log('Refreshing dashboard...');
    await initializeDashboard();
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', initializeDashboard);

// Auto-refresh every 5 minutes (optional)
// setInterval(refreshDashboard, 5 * 60 * 1000);
