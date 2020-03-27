"use strict";

var agenda = agenda || {};
agenda.meta = {
    version: '0.1'
}

/**
 *
 * Creare un head ed un body di struttura
 * head contiene intestazione con controller con frecce e (giorno) mese anno
 *
 * body contiene vista mese o vista giorno
 * le viste sono esclusive
 *
 * preparare una struttura per cui scelgo la modalità di vista (mese, giorno)
 * poi applico la stessa identica animazione al contenuto del body, prev e next
 *
 * evitare layer e rimanere sempre responsive. si esce da responsive solo durante animazione
 *
 */

/**
 * Locale class
 */
agenda.Locale = new Class({
    initialize: function(dict, locale) {
        this.dict = dict;
        this.lng = locale;
    },
    /**
     * Sets a new locale
     * @param {String} locale
     * @return void
     */
    set: function(locale) {
        this.lng = locale;
    },
    /**
     * Gets a locale string from key
     * @param {String} key
     * @return {String} localized string
     */
    get: function(key) {
        if(typeof this.lng != 'undefined' && typeof this.dict[this.lng] != 'undefined' && typeof this.dict[this.lng][key] != 'undefined') {
            return this.dict[this.lng][key];
        }

        return key;
    }
});

/**
 * Calendar class
 * 
 * Options:
 * - day_chars (integer), number of days characters
 * - json_url (string), request json url
 * - json_params (string), request json params
 * - month_view_ctrl (string), span id of the calendar controller for activate the link to return in calendar view from items list view;
 * 								if null, the link is directly in items list view
 * - hide_container (string), tag id of a container to hide after the month request
 */
agenda.Calendar = new Class({
    Implements: [Options, Events],
    options: {
        css_prefix: 'k',
        monday_first_week_day: true,
        day_chars: 1,
        json_url: '/',
        json_params: null,
        month_view_ctrl: null,
        hide_container: null,
        onComplete: function() {}
    },
    /**
     * Calendar initialization
     * @param {Object} options
     */
    initialize: function(options) {
        this.setOptions(options);
        this.locale = new agenda.Locale(agenda.locale_dict, 'it_IT');
        this.today = new Date();
        this.current = new Date(); // changes while navigating
        this.request_data = '';
        this.dom = {};
        this.month_keys = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december']; // month_keys[d.getMonth()])
        this.day_keys = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        this.view_state = 'month';
        this.create();
    },
    setRequestData: function(data) {
        this.request_data = data;
    },
    /**
     * Css class with prefix
     * @param {String} class_name
     * @return {String} css class with prefix
     */
    css: function(class_name) {
        return this.options.css_prefix + '-' + class_name;
    },
    /**
     * Creates the calendar, not rendering
     * @return void
     */
    create: function() {
        this.dom.container = new Element('div.' + this.css('container'))
            .adopt(
                this.dom.head_container = new Element('div.' + this.css('head-container'))
                    .adopt(this.dom.head = this.create_head()),
                this.dom.body_container = new Element('div.' + this.css('body-container')).setStyles({
                    position: 'relative'
                })
            );
    },
    create_head: function() {
        var head = new Element('table.' + this.css('table-head'))
            .adopt(new Element('tr').adopt(
                new Element('td.' + this.css('ctrl-prev')).adopt(this.dom.prev_nav = new Element('span.fa.fa-chevron-left')),
                new Element('td').adopt(
                    this.dom.current_day = new Element('span.' + this.css('current-day')),
                    this.dom.current_month = new Element('span.' + this.css('current-month')),
                    this.dom.current_year = new Element('span')
                ),
                new Element('td.' + this.css('ctrl-next')).adopt(this.dom.next_nav = new Element('span.fa.fa-chevron-right')
                    .addEvent('click', function() {
                        this.current.setMonth(this.current.getMonth() + 1); 
                        this.fireEvent('monthchanged', 'next')
                    }.bind(this))
                )
            ));
        return head;
    },
    render: function(inject_to) {

        this.addEvent('monthanimationcomplete', this.onMonthAnimationComplete.bind(this));
        this.addEvent('dayanimationcomplete', this.onDayAnimationComplete.bind(this));
        $(inject_to).adopt(this.dom.container);
        this.spinner = new Spinner(this.dom.container, {
            'class': 'spinner-container'
        });
        this['activate' + this.view_state + 'State']();
        this.fireEvent('complete');
    },
    getCurrentMonth: function() {
        return this.current.getMonth();
    },
    getCurrentYear: function() {
        return this.current.getFullYear();
    },
    activatemonthState: function(opts) {
        var opts_dft = {
            request: true
        };
        var options = Object.merge(opts_dft, opts);
        // set head
        this.dom.current_day.set('text', '');
        this.dom.current_month.set('text', this.locale.get(this.month_keys[this.current.getMonth()]));
        this.dom.current_year.set('text', this.current.getFullYear());
        this.dom.prev_nav.removeEvents('click')
            .addEvent('click', function() {
                this.current.setMonth(this.current.getMonth() - 1);
                this.changeMonth('prev');
            }.bind(this));
        this.dom.next_nav.removeEvents('click')
            .addEvent('click', function() {
                this.current.setMonth(this.current.getMonth() + 1);
                this.changeMonth('next');
            }.bind(this));

        // set body
        this.dom.body = this.create_month_body();
        this.dom.body_container.empty().adopt(this.dom.body);

        if(options.request) {
            this.requestMonthData();
        }
        else {
            this.addMonthData();
        }

    },
    create_month_body: function() {
        this.d = new Date(this.current.toDateString()); // renders dates
        var body = new Element('table.' + this.css('table-body'));
        body.adopt(new Element('tr').adopt(this.month_head_day_cells()));
        body.adopt(this.month_cells());

        return body;

    },
    /**
     * Creates the first body row, with day labels
     */
    month_head_day_cells: function() {
        var day_cells = [];
        if(!this.options.monday_first_week_day) {
            day_cells.push(new Element('th').set('text', this.locale.get(this.day_keys[6]).substr(0, this.options.day_chars)));
        }
        for(var i = 0; i < (!this.options.monday_first_week_day ? 6 : 7); i++) {
            day_cells.push(new Element('th').set('text', this.locale.get(this.day_keys[i]).substr(0, this.options.day_chars)));
        }

        return day_cells;
    },
    /**
     * Creates the month view
     */
    month_cells: function() {
        this.d.setDate(1);
        var row_cells = [];
        var rows = [];
        // prev month days
        var prev_cells_l = this.options.monday_first_week_day ? (this.d.getDay() + 6) % 7 : this.d.getDay();

        this.d.setDate((prev_cells_l - 1) * -1);

        for(var i = 0; i < 43; i++) {
            if(i % 7 == 0) {
                if(row_cells.length) {
                    rows.push(new Element('tr').adopt(row_cells));
                }
                row_cells = [];
            }
            var classes = [this.css('day')];
            if(this.d.toDateString() == this.today.toDateString()) classes.push(this.css('today'));
            if(this.d.getMonth() != this.current.getMonth()) classes.push(this.css('other_month'))
            row_cells.push(new Element('td[data-year=' + this.d.getFullYear() + '][data-month=' + (this.d.getMonth() + 1) +'].' + classes.join('.')).set('text', this.d.getDate()));
            this.d.setDate(this.d.getDate() + 1);
        }

        return rows;

    },
    requestMonthData: function() {
        var self = this;
        
        if(this.options.json_params) {
        	var jparam = this.options.json_params + '&';
        }
        else {
        	var jparam = '';
        }
        // request month items
        var request = new Request.JSON({
        	url: this.options.json_url,
            method: 'post',
            data: jparam + 'month=' + (this.current.getMonth() + 1) + '&year=' + this.current.getFullYear() + (this.request_data ? '&' + this.request_data : ''),
            onRequest: function() {
                self.spinner.show();
            },
            onFailure: function() {
                self.spinner.hide();
                alert(self.locale.get('cant-fetch-data'));
            },
            onError: function() {
                self.spinner.hide();
                alert(self.locale.get('invalid-data'));
            },
            onSuccess: function(responseJSON, responseText) {
                self.items = responseJSON;
                self.addMonthData();
                self.spinner.hide();
                self.hideContainerOnMonthChange();
            }
        }).send();

    },
    hideContainerOnMonthChange: function() {
    	if(this.options.hide_container !== null) {
    		$(this.options.hide_container).setStyles({
                display: 'none'
            });
    	}
    },
    addMonthData: function() {
        this.dom.body.getElements('.' + this.css('day')).each(function(cell) {
            var day = cell.get('text');
            var month = cell.get('data-month');
            var year = cell.get('data-year');
            if(this.dayHasItems(day, month, year)) {
                cell.addClass(this.css('filled'))
                    .addEvent('click', this.activatedayState.bind(this, [day, month, year]));
            }
            else {
                cell.removeClass(this.css('filled'))
                    .removeEvents('click');
            }
        }.bind(this));
    },
    dayHasItems: function(day, month, year) {
        for(var i = 0, l = this.items.length; i < l; i++) {
            var item = this.items[i];
            if(item.day.toInt() == day && item.month.toInt() == month && item.year.toInt() == year) {
                return true;
            }
        }
        return false;
    },
    getDayItems: function(day, month, year) {
        var res = [];
        for(var i = 0, l = this.items.length; i < l; i++) {
            var item = this.items[i];
            if(item.day.toInt() == day && item.month.toInt() == month && item.year.toInt() == year) res.push(item);
        }
        return res;
    },
    changeMonth: function(dir) {
        var self = this;

        this.dom.current_month.set('text', this.locale.get(this.month_keys[this.current.getMonth()]));
        this.dom.current_year.set('text', this.current.getFullYear());

        // fix body and container dim for animation
        var body = this.create_month_body();
        this.animate(body, dir, 'monthanimationcomplete');
    },
    animate: function(sub, dir, evt) { // this.body_container, this.body, body
        var self = this;
        var mprop = dir == 'prev' ? 'right' : 'left';
        var cont_dim = this.dom.body_container.getCoordinates();
        var replaced_dim = this.dom.body.getCoordinates(this.dom.body_container);
        // fix dimensions
        this.dom.body_container.setStyles({
            height: cont_dim.height + 'px',
            overflow: 'hidden'
        });
        this.dom.body.setStyles({
            width: replaced_dim.width + 'px',
            height: replaced_dim.height + 'px',
            position: 'absolute',
            top: replaced_dim.top
        });
        this.dom.body.setStyle(mprop, 0);
        sub.setStyles({
            width: replaced_dim.width + 'px',
            height: replaced_dim.height + 'px',
            position: 'absolute',
            top: replaced_dim.top
        });
        sub.setStyle(mprop, cont_dim.width + 'px');
        sub.inject(this.dom.body_container);
        // css3 transition
        setTimeout(function() {
            self.dom.body.setStyle(mprop, -1 * replaced_dim.width);
            sub.setStyle(mprop, 0);
            setTimeout(function() {
                self.dom.body.destroy();
                self.dom.body = sub;
                // unfix dim to get responsive again
                sub.setStyles({
                    width: '',
                    height: '',
                    position: 'static',
                    top: ''
                });
                sub.setStyle(mprop, '');
                self.dom.body_container.setStyles({
                    height: ''
                });
                self.fireEvent(evt);
            }, 200);
        }, 100);
    },
    onMonthAnimationComplete: function() {
        this.requestMonthData();
    },
    activatedayState: function(day, month, year) {
        if(month != this.current.getMonth() + 1 || year != this.current.getFullYear()) {
            this.current = new Date(year, month - 1, day);
            this.requestMonthData();
        }
        else {
            this.current = new Date(year, month - 1, day);
        }
        // set head
        this.dom.current_day.set('text', this.locale.get(this.day_keys[(this.current.getDay() + 6) % 7]) + ' ' + this.current.getDate());
        this.dom.current_month.set('text', this.locale.get(this.month_keys[this.current.getMonth()]));
        this.dom.current_year.set('text', this.current.getFullYear());

        this.dom.prev_nav.removeEvents('click')
            .addEvent('click', function() {
                this.searchDay('prev');
            }.bind(this));
        this.dom.next_nav.removeEvents('click')
            .addEvent('click', function() {
                this.searchDay('next');
            }.bind(this));

        // set body
        // fix container dim
        var container_dim = this.dom.container.getCoordinates();
        this.dom.container.setStyles({
            width: container_dim.width + 'px',
            height: container_dim.height + 'px',
            overflow: 'hidden'
        });
        // fix body container dim
        var head_dim = this.dom.head_container.getCoordinates();
        this.dom.body_container.setStyles({
            height: (container_dim.height - head_dim.height) + 'px',
            overflow: 'auto'
        });

        if(this.options.month_view_ctrl === null) {
            var month_view_ctrl = new Element('span.fa.fa-calendar.fa-2x').setStyles({
                position: 'absolute',
                bottom: '5px',
                right: '15px'
            }).inject(this.dom.container)
        }
        else {
            var month_view_ctrl = $(this.options.month_view_ctrl);
            month_view_ctrl.addClass('enabled');
        }
        month_view_ctrl.addEvent('click', function() {
            // unfix container dim to get responsive again
            this.dom.container.setStyles({
                width: '',
                height: '',
                overflow: ''
            });
            this.dom.body_container.setStyles({
                height: '',
                overflow: ''
            });
            this.activatemonthState({request: false});
            if(this.options.month_view_ctrl === null) {
                month_view_ctrl.destroy();
            }
            else {
                month_view_ctrl.removeClass('enabled');
                month_view_ctrl.removeEvents('click');
            }
        }.bind(this));

        this.dom.body = this.create_day_body();
        this.dom.body_container.empty().adopt(this.dom.body);
    },
    create_day_body: function() {
        var day = this.current.getDate();
        var month = this.current.getMonth();
        var year = this.current.getFullYear();
        var items = this.getDayItems(day, month+1, year);
        var ul = new Element('ul');
        var li_items = [];
        for(var i = 0, l = items.length; i < l; i++) {
            var item = items[i];
            li_items.push(
                new Element('li').adopt(
                    new Element('span.dt').adopt((item.modal == false ? new Element('a[href=' + item.url + ']') : new Element('a', {
                    	'href': item.onclick.href, 
                    	'class': item.onclick.prop_class, 
                    	'data-type': item.onclick.prop_type, 
                    	'data-esc-close': item.onclick.prop_esc_close, 
                    	'data-overlay': item.onclick.prop_overlay, 
                    	'data-title': item.onclick.prop_title
                    })).set('text', item.name)),
                    new Element('span.dd').set('html', item.description)
                )
            )
        }

        return new Element('div.' + this.css('day-content')).adopt(ul.adopt(li_items));
    },
    searchDay: function(dir) {
        var day = this.current.getDate();
        var month = this.current.getMonth();
        var year = this.current.getFullYear();
        while(day > 0 && day < 32) {
            var day = dir == 'prev' ? day - 1 : day + 1;
            if(this.dayHasItems(day, month + 1, year)) {
                this.current.setDate(day);
                this.changeDay(dir);
                break;
            }
        }
    },
    changeDay: function(dir) {
        var self = this;

        this.dom.current_day.set('text', this.locale.get(this.day_keys[(this.current.getDay() + 6) % 7]) + ' ' + this.current.getDate());
        this.dom.current_month.set('text', this.locale.get(this.month_keys[this.current.getMonth()]));
        this.dom.current_year.set('text', this.current.getFullYear());

        // fix body and container dim for animation
        var body = this.create_day_body();
        this.animate(body, dir, 'dayanimationcomplete');
    },
    onDayAnimationComplete: function() {
        var container_dim = this.dom.container.getCoordinates();
        this.dom.container.setStyles({
            width: container_dim.width + 'px',
            height: container_dim.height + 'px',
            overflow: 'hidden'
        });
        // fix body container dim
        var head_dim = this.dom.head_container.getCoordinates();
        this.dom.body_container.setStyles({
            height: (container_dim.height - head_dim.height) + 'px',
            overflow: 'auto'
        });
    },
});
