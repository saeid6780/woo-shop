/**
 * Mobile navigation toggle
 * @param {mixed} event
 */

const toggleMenu = (event) => {
    event.preventDefault();
    jQuery('.js-menu-toggle').toggleClass('open');
    jQuery('body').toggleClass('menu-open');
    jQuery('.header__navigation').fadeToggle();
};

jQuery('.js-menu-toggle').on('click', toggleMenu);

jQuery(function ($) {
    'use strict';

    // A map to store Swiper instances to re-initialize them
    const swiperInstances = {};

    function initializeResponsiveSlider(selector, options) {
        const containers = document.querySelectorAll(selector);
        containers.forEach(container => {
            const containerId = container.id || 'slider-' + Math.random().toString(36).substr(2, 9);
            container.id = containerId;

            const toggleSwiper = () => {
                const screenWidth = window.innerWidth;
                if (screenWidth < 992 && !swiperInstances[containerId]) {
                    swiperInstances[containerId] = new Swiper(container, options);
                } else if (screenWidth >= 992 && swiperInstances[containerId]) {
                    swiperInstances[containerId].destroy(true, true);
                    swiperInstances[containerId] = null;
                }
            };
            toggleSwiper();
            $(window).on('resize', toggleSwiper);
        });
    }

    function handleTabClicks() {
        $('.websima-product-tabs').on('click', '.tab-button', function (e) {
            e.preventDefault();
            const $button = $(this);

            if ($button.hasClass('is-active')) return;

            const termId = $button.data('term-id');
            const level = $button.data('level');
            const targetPanelId = $button.attr('aria-controls');
            const $targetPanel = $('#' + targetPanelId);


            // --- 1. NEW LOGIC: Update Section Header if it's a Level 1 Tab ---
            if (level === 1) {
                const $header = $('.websima-product-tabs__header');
                const $title = $header.find('.section-title');
                const $icon = $header.find('.section-icon');
                const $archiveLink = $('.websima-product-tabs__archive-link');
                const $archiveLinkText = $('.archive-link-text');

                // Get new data from the clicked button's data attributes
                const newTitle = $button.data('term-name');
                const newIconUrl = $button.data('icon-url');
                const newArchiveLinkUrl = $button.data('archive-link');
                const newArchiveLinkText =  'فروشگاه' + ' ' + newTitle; // Recreate the text

                // Update elements with a smooth fade effect
                $header.fadeOut(200, function() {
                    // Update content
                    $title.text(newTitle);
                    $archiveLink.attr('href', newArchiveLinkUrl);
                    $archiveLinkText.text(newArchiveLinkText);

                    // Update icon (and hide if it doesn't exist)
                    if (newIconUrl) {
                        $icon.attr('src', newIconUrl).attr('alt', 'آیکون ' + newTitle ).show();
                    } else {
                        $icon.hide();
                    }
                    // Fade the header back in
                    $(this).fadeIn(200);
                });
            }

            // --- 2. Existing Logic for handling tabs and panels ---
            $button.closest('.tabs-list, .swiper-wrapper').find('.tab-button').removeClass('is-active').attr('aria-selected', 'false');
            $button.addClass('is-active').attr('aria-selected', 'true');

            // Update panel states
            const $panelWrapper = $targetPanel.parent();
            $panelWrapper.children('.tab-panel').removeClass('is-active').attr('hidden', true);
            $targetPanel.addClass('is-active').removeAttr('hidden');

            // --- AJAX & Caching Logic ---
            if ($targetPanel.data('loaded')) {
                // Content is already loaded (client-side cache), just show it
                return;
            }

            // Show loading state
            const loadingHTML = '<div class="loading-state"><span class="loader"></span></div>';
            $targetPanel.html(loadingHTML);

            // AJAX request
            $.post(websima_ajax.ajax_url, {
                action: 'fetch_tab_content',
                nonce: websima_ajax.nonce,
                term_id: termId,
                level: level
            })
                .done(function (response) {
                    if (response.success) {
                        $targetPanel.html(response.data).data('loaded', true); // Set content and mark as loaded
                        // Initialize sliders for the new content
                        initializeAllSliders();
                    } else {
                        $targetPanel.html('<p>Error loading content.</p>');
                    }
                })
                .fail(function () {
                    $targetPanel.html('<p>An unexpected error occurred.</p>');
                });
        });
    }

    function initializeAllSliders() {
        initializeResponsiveSlider('.product-slider', {
            loop: false,
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: { el: '.product-slider-pagination', clickable: true },
            breakpoints: {
                768: { slidesPerView: 3, spaceBetween: 20 },
                576: { slidesPerView: 2, spaceBetween: 30 },
            }
        });

        initializeResponsiveSlider('.tabs-slider', {
            loop: false,
            slidesPerView: 'auto',
            spaceBetween: 15,
            pagination: { el: '.tabs-slider-pagination', clickable: true }
        });
    }

    // --- INITIALIZE ---
    initializeAllSliders();
    handleTabClicks();
});