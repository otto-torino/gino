/*
---
description: Add autocomplete functionality to input elements, provides tag and normal text autocmplete modes.

license: MIT-style

authors:
- Erik Dubbelboer

requires:
- Core/Element.Event
- Core/Element.Style

provides: [MooComplete]

...
*/

/*
tag mode and some other minor changes provided by abidibo <abidibo@gmail.com> <http://www.abidibo.net> <http://github.com/abidibo>
*/

// options should be an object and can contain the following members:
//  - list: Array              the list of elements to autocomplete from
//  - size: number             the number of elements to suggest
//  - mode: string             the autocomplete mode ('tag' or 'text'), default 'text'
//  - render: function(value)  the function called when rendering an element from the list
//  - get: function(value)     the function called when testing the value against the input
//  - set: function(value)     the function called when putting an element from the list into the input element (detauls to the get function)
//  - filters: Array           list of functions to filter suggestions. The functions should take two arguments, the first is the suggestion from the list, the second is the user input. If a function returns true the suggestion will be added to the list.
function MooComplete(element, options) {
	options = options || {};

	var list = options.list || [];

	this.setList = function(l) {
		list = l;
	}

	// First add suggestions that match the start, then suggestions that match the middle.
	if (!options.filters) {
		options.filters = [
			function(o, v) { return (o.indexOf(v) == 0); },
			function(o, v) { return ((v.length > 1) && (o.indexOf(v) > 0)); }
		];
	}

	options.size = options.size || 10;

	// tag mode | text mode others in future?
	options.mode = options.mode || 'text';


	if (!options.render) {
		// Default render function assumes a list of strings and just puts a span around it.
		options.render = function(v) {
			return $('<span \>', {
				'text': v
			});
		};
	}

	if (!options.get) {
		// Default get function assumes a list of strings so just return the string.
		options.get = function(v) {
			return v;
		};
	}

	if (!options.set) {
		// The default function is the same as the get function.
		options.set = options.get;
	}

	// allow id and dom object selection
	element = jQuery.type(element)==='string' ? $(element) : element;

	// For older versions of IE this doesn't work, for those you need to set autocomplete=off in the html.
	element.attr('autocomplete', 'off');

	// Disable auto correct and capitalize on iPhone and iPad.
	element.attr('autocorrect', 'off');
	element.attr('autocapitalize', 'off');

	var box = $('<div \>', {
		'class':  'moocomplete',
		'styles': {
			'position': 'absolute',
			'display':  'none'
		}
	}).appendTo(document.body);

	var old,
	hover       = -1,
	hiding      = false,
	suggestions = 0;

	// Update the position of the box.
	function updatePosition() {
	  
		box.css({
			'width': ($(element).width() - 2) + 'px',
			'top':   ($(element).position().top + $(element).height()) + 'px',
			'left':  $(element).position().left +'px'
		});
	}

	$(window).on('resize', updatePosition);

  // get element value to search for
  function getNeedle() {
    if (options.mode === 'tag') {
      element.store('input_value', element.value.substring(0, element.value.lastIndexOf(',') + 1));

      return element.val(substr(element.value.lastIndexOf(',') + 1 || 0).toLowerCase().trim());
    } else {
      return element.value.toLowerCase();
    }
  }

  // Show suggestions for current input.
  function showSuggestions() {
    var v = getNeedle();

    if (v.length == 0) {
      box.css('display', 'none');
      return;
    }

    suggestions = 0;

    box.empty();

    options.filters.each(function(i, f) {
      if (suggestions == options.size) {
        return;
      }

      list.every(function(o) {
        if (f(options.get(o).toLowerCase(), v)) {
          var li = suggestions++;

          box.append($('<div \>', {
            'events': {
              'mousemove': function() { // don't use mouseover since that will bug when the user has the mouse below the input box while typing
                if (!hiding) {
                  hover = li;
                  showHover();
                }
              }
            }
          }).append(options.render(o)).store('val', o));

          if (suggestions == options.size) {
            return false;
          }
        }

        return true;
      });
    });

    updatePosition();

    // If no suggestions, no need to show the box
    if (suggestions > 0) {
        box.css('display', 'block');
    } else {
        box.css('display', 'none');
    }
  }

  // Highlight hovered item and place it in the input field
  function showHover() {
    var c = box.children();

    c.removeClass('hovered');

    if (hover >= 0) {
      c[hover].addClass('hovered');

      if (options.mode==='tag') {
        element.val(element.retrieve('input_value') + options.set(c[hover].retrieve('val')));
      } else {
        element.val(options.set(c[hover].retrieve('val')));
      }
    }
  }


  element.on({
    'keydown': function(e) {
      if (box.css('display') == 'none') {
        return;
      }

      if (e.code == 38) { // up
        if (hover >= 0) {
          if (hover == 0) {
            element.val(options.set(old));
          }

          --hover;
          showHover();
        }
      } else if (e.code == 40) { // down
        if (hover < (suggestions - 1)) {
          ++hover;
          showHover();
        }
      } else {
        hover = -1;
        // No need to update the hovered item since we are redrawing the suggestions anyways
      }
    },
    'keyup': function(e) {
      if (e.code == 27) { // escape
        box.css('display', 'none');
      } else if ((e.code != 38) && // up
                 (e.code != 40)) { // down
        old = element.retrieve('val');

        if (e.code != 13) { // enter
          showSuggestions();
        } else {
          box.css('display', 'none');
        }
      }
    },
    'focus': function() {
      hiding = false;

      if (box.css('display') == 'none') {
        showSuggestions();
      }
    },
    'blur': function() {
      hover  = -1;
      old    = element.retrieve('val');
      hiding = true;

      (function() {
        box.css('display', 'none');
      }).delay(100);
    },
    'mousemove': function() {
      if (hover >= 0) {
        element.val(options.set(old));
        hover = -1;
        showHover();
      }
    }
  });
}

