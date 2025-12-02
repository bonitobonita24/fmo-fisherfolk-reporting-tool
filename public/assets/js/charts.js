/**
 * Charts.js - Main JavaScript for Fisherfolk Dashboard
 * Calapan City FMO - Powerbyte IT Solutions
 * Uses Chart.js for data visualization
 */

// Configuration
const API_BASE_URL = '/api';
const THEME_COLORS = {
    primary: '#F28500',    // Tangerine Orange
    secondary: '#0000FF',  // Blue
    success: '#10b981',
    info: '#3b82f6',
    warning: '#f59e0b',
    danger: '#ef4444',
    pink: '#ec4899',
    green: '#22c55e'
};

// Chart.js default configuration
Chart.defaults.color = '#666';
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

// Global chart instances
let barangayChart, genderChart, ageGroupChart, categoryChart, barangayCategoryChart;

// Global data storage for filtering/sorting
let allFisherfolkData = [];
let filteredFisherfolkData = [];

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
    const selectList = document.getElementById('barangayFilterList');
    
    // Add barangay options to both dropdowns
    data.forEach(item => {
        const option1 = document.createElement('option');
        option1.value = item.barangay;
        option1.textContent = item.barangay;
        select.appendChild(option1);
        
        const option2 = document.createElement('option');
        option2.value = item.barangay;
        option2.textContent = item.barangay;
        selectList.appendChild(option2);
    });
    
    // Add event listener for chart filter change
    select.addEventListener('change', function() {
        createBarangayCategoryChart(this.value);
        loadFisherfolkList(this.value);
    });
    
    // Add event listener for list filter change
    selectList.addEventListener('change', function() {
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
    
    if (!data || data.length === 0) {
        allFisherfolkData = [];
        filteredFisherfolkData = [];
        displayFisherfolkList([]);
        return;
    }
    
    // Store data globally
    allFisherfolkData = data;
    
    // Apply current filters and sort
    applyFiltersAndSort();
}

/**
 * Apply search, filter, and sort to fisherfolk list
 */
function applyFiltersAndSort() {
    let data = [...allFisherfolkData];
    
    // Apply search filter
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    if (searchTerm) {
        data = data.filter(item => {
            const idMatch = (item.id_number || '').toLowerCase().includes(searchTerm);
            const nameMatch = (item.full_name || '').toLowerCase().includes(searchTerm);
            const rsbsaMatch = (item.rsbsa || '').toLowerCase().includes(searchTerm);
            return idMatch || nameMatch || rsbsaMatch;
        });
    }
    
    // Apply category filter
    const categoryFilter = document.getElementById('categoryFilter').value;
    if (categoryFilter !== 'all') {
        data = data.filter(item => item[categoryFilter] == 1);
    }
    
    // Apply sort
    const sortBy = document.getElementById('sortBy').value;
    data.sort((a, b) => {
        switch(sortBy) {
            case 'name_asc':
                return (a.full_name || '').localeCompare(b.full_name || '');
            case 'name_desc':
                return (b.full_name || '').localeCompare(a.full_name || '');
            case 'id_asc':
                return (a.id_number || '').localeCompare(b.id_number || '');
            case 'id_desc':
                return (b.id_number || '').localeCompare(a.id_number || '');
            case 'barangay_asc':
                return (a.address || '').localeCompare(b.address || '');
            case 'barangay_desc':
                return (b.address || '').localeCompare(a.address || '');
            default:
                return 0;
        }
    });
    
    filteredFisherfolkData = data;
    displayFisherfolkList(data);
}

/**
 * Display fisherfolk list in table
 */
function displayFisherfolkList(data) {
    const tbody = document.getElementById('fisherfolkTableBody');
    const noDataMsg = document.getElementById('noDataMessage');
    const resultCount = document.getElementById('resultCount');
    
    // Update result count
    resultCount.textContent = data.length;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '';
        noDataMsg.classList.remove('hidden');
        return;
    }
    
    noDataMsg.classList.add('hidden');
    
    // Build table rows with Tailwind classes
    tbody.innerHTML = data.map((fisherfolk, index) => {
        // Build activity categories badges with Tailwind
        const categories = [];
        if (fisherfolk.boat_owneroperator == 1) categories.push('<span class="inline-block bg-primary text-white text-xs px-2 py-1 rounded mr-1 mb-1">Boat Owner/Operator</span>');
        if (fisherfolk.capture_fishing == 1) categories.push('<span class="inline-block bg-green-500 text-white text-xs px-2 py-1 rounded mr-1 mb-1">Capture Fishing</span>');
        if (fisherfolk.gleaning == 1) categories.push('<span class="inline-block bg-blue-500 text-white text-xs px-2 py-1 rounded mr-1 mb-1">Gleaning</span>');
        if (fisherfolk.vendor == 1) categories.push('<span class="inline-block bg-yellow-500 text-white text-xs px-2 py-1 rounded mr-1 mb-1">Vendor</span>');
        if (fisherfolk.fish_processing == 1) categories.push('<span class="inline-block bg-red-500 text-white text-xs px-2 py-1 rounded mr-1 mb-1">Fish Processing</span>');
        if (fisherfolk.aquaculture == 1) categories.push('<span class="inline-block bg-gray-600 text-white text-xs px-2 py-1 rounded mr-1 mb-1">Aquaculture</span>');
        
        const categoriesHtml = categories.length > 0 ? categories.join(' ') : '<span class="text-gray-400 text-sm">None</span>';
        
        const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
        
        // Handle image with placeholder fallback and loading state
        // If image is just a filename (no path), prepend /images/
        let imageUrl = '/assets/images/face.png'; // default placeholder
        if (fisherfolk.image && fisherfolk.image.trim() !== '') {
            const imgPath = fisherfolk.image.trim();
            // Check if it's just a filename (no / or http)
            if (!imgPath.includes('/') && !imgPath.startsWith('http')) {
                imageUrl = '/images/' + imgPath;
            } else {
                imageUrl = imgPath;
            }
        }
        
        const escapedName = (fisherfolk.full_name || '').replace(/'/g, "\\'");
        const escapedUrl = imageUrl.replace(/'/g, "\\'");
        
        const imageHtml = `
            <div class="relative w-12 h-12">
                <img src="${imageUrl}" 
                     alt="${fisherfolk.full_name}" 
                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 cursor-pointer hover:opacity-75 transition-opacity bg-gray-100"
                     onclick="openImageModal('${escapedUrl}', '${escapedName}')"
                     onerror="this.onerror=null; this.src='/assets/images/face.png';"
                     loading="lazy">
            </div>`;
        
        return `
            <tr class="${rowClass} hover:bg-orange-50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">${imageHtml}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${fisherfolk.id_number || 'N/A'}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${fisherfolk.full_name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${fisherfolk.rsbsa || 'N/A'}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${fisherfolk.address}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${fisherfolk.sex}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${fisherfolk.contact_number || 'N/A'}</td>
                <td class="px-4 py-3 text-sm">${categoriesHtml}</td>
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

/**
 * Setup search and filter event listeners
 */
function setupSearchAndFilters() {
    // Search input
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        applyFiltersAndSort();
    });
    
    // Clear search button
    const clearSearch = document.getElementById('clearSearch');
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        applyFiltersAndSort();
    });
    
    // Category filter
    const categoryFilter = document.getElementById('categoryFilter');
    categoryFilter.addEventListener('change', function() {
        applyFiltersAndSort();
    });
    
    // Sort dropdown
    const sortBy = document.getElementById('sortBy');
    sortBy.addEventListener('change', function() {
        applyFiltersAndSort();
    });
}

/**
 * Open image modal
 */
function openImageModal(imageUrl, name) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalName = document.getElementById('modalName');
    
    modalImage.src = imageUrl;
    modalName.textContent = name;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
}

/**
 * Close image modal
 */
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupSearchAndFilters();
});

// Auto-refresh every 5 minutes (optional)
// setInterval(refreshDashboard, 5 * 60 * 1000);
