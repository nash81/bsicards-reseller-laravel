/**
 * BSI Cards Admin - Professional UI Enhancements
 * Modern admin dashboard interactions
 */

(function($) {
    'use strict';

    // Sidebar Toggle Functionality
    const initSidebarToggle = () => {
        const $layout = $('.layout');
        const $sidebarToggle = $('.sidebar-toggle, #sidebar-toggle');
        const $sideNav = $('.side-nav');

        // Load sidebar state from localStorage
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'folded') {
            $layout.addClass('sidebar-folded');
        }

        // Toggle sidebar on button click
        $sidebarToggle.on('click', function(e) {
            e.preventDefault();
            $layout.toggleClass('sidebar-folded');

            // Save state to localStorage
            if ($layout.hasClass('sidebar-folded')) {
                localStorage.setItem('sidebarState', 'folded');
            } else {
                localStorage.setItem('sidebarState', 'expanded');
            }
        });

        // Mobile sidebar toggle
        if ($(window).width() <= 992) {
            $sidebarToggle.on('click', function(e) {
                e.preventDefault();
                $layout.toggleClass('sidebar-open');
            });

            // Close sidebar when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.side-nav, .sidebar-toggle').length) {
                    $layout.removeClass('sidebar-open');
                }
            });
        }
    };

    // Sidebar Dropdown Functionality
    const initSidebarDropdowns = () => {
        $('.side-nav-dropdown > .dropdown-link').on('click', function(e) {
            e.preventDefault();

            const $parent = $(this).parent('.side-nav-dropdown');

            // Toggle current dropdown
            $parent.toggleClass('nav-item-open');

            // Close other dropdowns (optional - comment out for accordion behavior)
            // $('.side-nav-dropdown').not($parent).removeClass('nav-item-open');
        });

        // Keep dropdowns open if they have active items
        $('.side-nav-dropdown').each(function() {
            const $dropdown = $(this);

            // Check for active class from Laravel's isActive helper
            if ($dropdown.hasClass('active') ||
                $dropdown.hasClass('nav-item-active') ||
                $dropdown.find('.active').length > 0 ||
                $dropdown.find('.nav-item-active').length > 0) {
                $dropdown.addClass('nav-item-open');
            }
        });

        // Also add nav-item-active class to parent if child is active
        $('.side-nav-item .dropdown-items .active').closest('.side-nav-dropdown').addClass('nav-item-active');
        $('.side-nav-item .dropdown-items .nav-item-active').closest('.side-nav-dropdown').addClass('nav-item-active');
    };

    // Smooth Scroll to Top
    const initScrollToTop = () => {
        const $scrollBtn = $('<button class="scroll-to-top"><i data-lucide="arrow-up"></i></button>');
        $('body').append($scrollBtn);

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                $scrollBtn.addClass('show');
            } else {
                $scrollBtn.removeClass('show');
            }
        });

        $scrollBtn.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 600);
        });
    };

    // Enhanced Tooltips
    const initTooltips = () => {
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    };

    // Card Animations
    const initCardAnimations = () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.5s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);

                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.data-card, .site-card').forEach(card => {
            observer.observe(card);
        });
    };

    // Initialize Lucide Icons
    const initLucideIcons = () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    // Form Input Enhancements
    const initFormEnhancements = () => {
        // Add focus class to form groups
        $('.form-control, .form-select').on('focus', function() {
            $(this).closest('.form-group').addClass('focused');
        }).on('blur', function() {
            $(this).closest('.form-group').removeClass('focused');
        });

        // Floating labels
        $('.form-control').each(function() {
            if ($(this).val()) {
                $(this).addClass('has-value');
            }
        });

        $('.form-control').on('change keyup', function() {
            if ($(this).val()) {
                $(this).addClass('has-value');
            } else {
                $(this).removeClass('has-value');
            }
        });
    };

    // Data Table Enhancement
    const initDataTableEnhancements = () => {
        // Add responsive wrapper if not exists
        $('.data-table').each(function() {
            if (!$(this).parent().hasClass('table-responsive')) {
                $(this).wrap('<div class="table-responsive"></div>');
            }
        });
    };

    // Page Loader
    const initPageLoader = () => {
        $(window).on('load', function() {
            $('.page-loader').fadeOut('slow');
        });
    };

    // Responsive Sidebar Auto-close
    const initResponsiveSidebar = () => {
        if ($(window).width() <= 992) {
            $('.side-nav-item > a, .dropdown-items a').on('click', function() {
                setTimeout(() => {
                    $('.layout').removeClass('sidebar-open');
                }, 300);
            });
        }
    };

    // Initialize all functions
    const init = () => {
        initSidebarToggle();
        initSidebarDropdowns();
        initTooltips();
        initFormEnhancements();
        initDataTableEnhancements();
        initResponsiveSidebar();

        // Initialize icons after a short delay to ensure DOM is ready
        setTimeout(() => {
            initLucideIcons();
        }, 100);

        // Optional: Enable card animations (comment out if not needed)
        // initCardAnimations();
    };

    // Run on document ready
    $(document).ready(function() {
        init();

        // Reinitialize icons after any dynamic content load
        $(document).on('DOMContentLoaded', initLucideIcons);
    });

    // Reinitialize on window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initResponsiveSidebar();
        }, 250);
    });

})(jQuery);

// Add scroll to top button styles dynamically
const scrollToTopStyles = `
<style>
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 45px;
    height: 45px;
    background: var(--primary-color, #4f46e5);
    color: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.scroll-to-top.show {
    opacity: 1;
    visibility: visible;
}

.scroll-to-top:hover {
    background: var(--primary-hover, #4338ca);
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.5);
}

.scroll-to-top svg {
    width: 20px;
    height: 20px;
}
</style>
`;

if (document.head) {
    document.head.insertAdjacentHTML('beforeend', scrollToTopStyles);
}

