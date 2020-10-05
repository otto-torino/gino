
jQuery.noConflict();

// A solution to solve the conflict of Mootools-more with Bootstrap Button dropdowns.
// This is not a conflict in the general sense because it's not causing JavaScript errors in the logs.
// MooTools
window.addEvent('domready',function() {
    Element.prototype.hide = function() {
        //alert('Intercepted');
        // Do nothing
    };
});
