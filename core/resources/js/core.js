
window.core = {};

(function($, undefined) {

    core.Parallax = function () {
        this.activate = function() {
            // for each element with the class 'scrollspy'
            $$('.scrollspy').each(function eachElement() {
                // cache the jQuery object
                var $this = $(this);

                var position = $this.getPosition();
                var size = $this.getSize();

            });

            var self = this;
            this.vpWidth = window.innerWidth;
            this.vpHeight = window.innerHeight;
            $(window).addEvent('resize', function () { self.adaptFirstSection() });
            $(window).addEvent('scroll', function () { 
                var top = $(window).getScroll().y;
                var el = $$('.scroll-to-top')[0];
                if(top >= self.vpHeight) {
                    el.removeClass('hidden');
                }
                else {
                    if(!el.hasClass('hidden')) {
                        $$('.scroll-to-top')[0].addClass('hidden');
                    }
                }
            });
            this.adaptFirstSection(true);

            return this;
        }

        this.adaptFirstSection = function (first) {
            var size = $$('.section-1')[0].getSize();
            var height = size.y;
            var vph = window.innerHeight;
            // avoid bounce when mobile address bar disappears
            if (vph > height && (first || this.vpWidth !== window.innerWidth)) {
                $$('.section-1')[0].setStyle('min-height', vph + 'px');
            }
        }

        this.scrollTo = function(section_id) {
            var scroll_fx = new Fx.Scroll(window);
            scroll_fx.toElement($$('.section-' + section_id)[0]);
        }

        this.scrollToTop = function() {
            var scroll_fx = new Fx.Scroll(window);
            scroll_fx.toTop();
        }
    }
})($, undefined)
