// Main JavaScript for GPMS - AI-Driven Gatepass Management System

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle for Mobile & Desktop
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        if (mainContent) mainContent.classList.toggle('sidebar-active');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }

    // Active Navigation Link
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav a');

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });

    // Form Validation Enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger)';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'danger');
            }
        });
    });

    // Notification System
    window.showNotification = function (message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1000';
        notification.style.minWidth = '300px';
        notification.style.animation = 'slideIn 0.4s ease';

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.4s ease';
            setTimeout(() => notification.remove(), 400);
        }, 3000);
    };

    // Smooth Scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Table Row Click to View Details
    const tableRows = document.querySelectorAll('tbody tr[data-id]');
    tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            if (id) {
                window.location.href = `view-pass-detail.php?id=${id}`;
            }
        });
    });

    // Animated Counter for Stats
    function animateCounter(element, target, duration = 1000) {
        let current = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }

    // Animate stat cards on page load
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        const target = parseInt(stat.textContent);
        if (!isNaN(target)) {
            stat.textContent = '0';
            setTimeout(() => animateCounter(stat, target), 200);
        }
    });

    // Delete Confirmation
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Utility Functions
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

function formatTime(date) {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(date).toLocaleTimeString('en-US', options);
}

// Loading State Helper
function setLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = '<span class="loading"></span> Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.getAttribute('data-original-text') || 'Submit';
    }
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy', 'danger');
    });
}
