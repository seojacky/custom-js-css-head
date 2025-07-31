
jQuery(document).ready(function($) {
    // Handle tab clicks for custom JS head metabox
    $(document).on('click', '.custom-js-head-tabs a', function(e) {
        e.preventDefault();
        
        var targetLang = $(this).data('language');
        
        // Hide all tab contents
        $('.custom-js-head-content').hide();
        
        // Show the selected tab content
        $('#custom-js-head-content-' + targetLang).show();
        
        // Update active tab
        $('.custom-js-head-tabs a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Store the active tab in local storage
        if (window.localStorage) {
            localStorage.setItem('custom_js_head_active_tab', targetLang);
        }
        
        return false;
    });
    
    // Initialize tabs - select the active tab from local storage or URL
    function initCustomJsHeadTabs() {
        // Check if we have a language in the URL hash
        var hashLang = '';
        if (window.location.hash) {
            var matches = window.location.hash.match(/custom-js-tab-(\w+)/);
            if (matches && matches.length > 1) {
                hashLang = matches[1];
            }
        }
        
        // Check if we have a stored active tab
        var storedLang = '';
        if (window.localStorage) {
            storedLang = localStorage.getItem('custom_js_head_active_tab');
        }
        
        // Determine which tab to activate (priority: hash > stored > first tab)
        var activeLang = hashLang || storedLang || $('.custom-js-head-tabs a').first().data('language');
        
        // Trigger click on the appropriate tab
        $('.custom-js-head-tabs a[data-language="' + activeLang + '"]').trigger('click');
    }
    
    // Initialize tabs
    initCustomJsHeadTabs();
    
    // Handle language switcher in WPGlobus admin bar
    $(document).on('click', '.wpglobus-selector-link', function() {
        // Get the target language
        var targetLang = $(this).attr('href').match(/language=(\w+)/)[1];
        
        // Switch the custom JS head tab if it exists
        if ($('.custom-js-head-tabs a[data-language="' + targetLang + '"]').length) {
            setTimeout(function() {
                $('.custom-js-head-tabs a[data-language="' + targetLang + '"]').trigger('click');
            }, 100);
        }
    });
});
        