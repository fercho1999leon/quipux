/**
 * Nornix common JavaScript routines.
 * 
 * @version     0.5 (2008-03-11)
 */

/**
 * The root namespace for all Nornix JavaScript.
 */
if (!Nornix) var Nornix = {
/**
 * @namespace Namespace for Event functions
 */
	events:{}, 
/**
 * @namespace Namespace for Cookie functions
 */
	cookies:{},
/**
 * @namespace Namespace for CSS functions.
 */
	css:{},
/**
 * @namespace Namespace for DOM functions.
 */
	dom:{},
/**
 * @namespace Namespace for Utility functions.
 */
	util:{}};

if (document.addEventListener)
{
/**
 * Function to add events to objects.
 * @see http://www.quirksmode.org/blog/archives/2005/10/_and_the_winner_1.html
 * @see http://ejohn.org/projects/flexible-javascript-events/
 * @see http://dean.edwards.name/weblog/2005/12/js-tip1
 * @param {Object} obj object to add the event to
 * @param {String} type event type, like "load", "click"
 * @param {Function} fn the function that should be run when the event fires
 * @param {boolean} capture if true, indicates that the user wishes to initiate capture
 */
	Nornix.events.add = function ( obj, type, fn, capture )
	{
		obj.addEventListener( type, fn, capture );
	};
/**
 * Function to remove events from objects.
 * You should use the same parameters as in {@link event#addEvent} to remove the same event listener.
 * @see http://www.quirksmode.org/blog/archives/2005/10/_and_the_winner_1.html
 * @see http://ejohn.org/projects/flexible-javascript-events/
 * @see http://dean.edwards.name/weblog/2005/12/js-tip1
 * @param {Object} obj object to add the event to
 * @param {String} type event type, like "load", "click"
 * @param {Function} fn the function that should be run when the event fires
 * @param {boolean} capture if true, indicates that the user wishes to initiate capture
 */
	Nornix.events.remove = function ( obj, type, fn, capture )
	{
		obj.removeEventListener( type, fn, capture );
	};
}
else if (document.attachEvent)
{
	/**
	 * @ignore
	 */
	Nornix.events.add = function( obj, type, fn )
	{
		obj["e"+type+fn] = fn;
		obj[type+fn] = function()
		{
			var e = window.event;
			e.target = window.event.srcElement;
			obj["e"+type+fn]( e );
		};
		obj.attachEvent( "on"+type, obj[type+fn] );
	};
	/**
	 * @ignore
	 */
	Nornix.events.remove = function( obj, type, fn )
	{
		obj.detachEvent( "on"+type, obj[type+fn] );
		obj[type+fn] = null;
		obj["e"+type+fn] = null;
	};
}
else
{
	// sorry, no support!
	Nornix.events.add = Function;
	Nornix.events.remove = Function;
}

/**
 * Function to stop normal browser action on events.
 * Function you can call within your event handlers to stop them performing
 * the normal browser action or kill the event entirely.
 * @see http://www.twinhelix.com
 * @param {Event} e the event object
 * @param {boolean} c set to true to cancel event bubbling
 */
Nornix.events.cancel = function(e, c)
{
	e.returnValue = false;
	if (e.preventDefault) e.preventDefault();
	if (c)
	{
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	}
};

/**
 * Function to run an initialization after an element
 * has became available in the DOM.
 *
 * The element will be sent to the initializing function as an object.
 * @param {String} id ID of element to wait for
 * @param {Function} initFunc the function that should be run when the element is available
 * @param {number} interval time in milliseconds
 */
Nornix.events.delayedInit = function (id, initFunc, interval)
{
	var el;
	if (interval === undefined) interval = 10;
	var intervalId = window.setInterval( function ()
		{
			if (el = document.getElementById(id))
			{
				window.clearInterval(intervalId);
				initFunc(el);
			}
		},
		interval
	);
};

/**
 * Function to create a browser cookie.
 * @param {String} name name of cookie
 * @param {String} value value of cookie
 * @param {Number} days number of days browser should save the cookie
 */
Nornix.cookies.create = function (name, value, days)
{
	var expire;
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expire = "; expires="+date.toGMTString();
	}
	else
	{
		expire = "";
	}
	document.cookie = name+"="+value+expire+"; path=/";
};

/**
 * Function to read a browser cookie.
 * @param {String} name name of cookie
 * @return value of cookie
 * @type String
 */
Nornix.cookies.read = function (name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';'), i = 0, c;
	while (c = ca[i++])
	{
		c = Nornix.util.trim(c);
		if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
	}
	return '';
};

/**
 * Function to erase a browser cookie.
 * @param {String} name name of cookie
 */
Nornix.cookies.erase = function (name)
{
	createCookie(name, "", -1);
};

/**
 * Function to swap, remove or add a class name on an object.
 * @param {HTMLElement} el the object to work on
 * @param {String} oldstr the old class -- set to null to add class
 * @param {String} newstr the new class -- leave empty to remove class
 */
Nornix.css.swap = function (el, oldstr, newstr)
{
	if (!el) return;
	if (!el.className || el.className.length === 0)
	{
		el.className = newstr ? newstr : "";
		return;
	}
	var arr = el.className.split(" "), i = 0, t;
	while (t = arr[i++])
	{
		if (t === oldstr)
		{
			if (newstr)
			{
				arr[i-1] = newstr;
				el.className = arr.join(" ");
				return;
			}
			else
			{
				delete arr[i-1];
				el.className = arr.join(" ");
				return;
			}
		}
		else if (t === newstr)
		{
				el.className = arr.join(" ");
				return;
		}
	}
	if (newstr) arr[arr.length] = newstr;
	el.className = arr.join(" ");
};

/**
 * Function to add a class name on an object.
 * @param {HTMLElement} el the object to work on
 * @param {String} className the new class
 */
Nornix.css.add = function (el, className)
{
	return Nornix.css.swap(el, null, className);
};

/**
 * Function to remove a class name from an object.
 * @function
 * @param {HTMLElement} el the object to work on
 * @param {String} className class to remove
 */
Nornix.css.remove = function (el, className)
{
	return Nornix.css.swap;
}(); // create alias for swapClasses()

/**
 * Check if class exists on element.
 * @param {HTMLElement} el the object to work on
 * @param {String} s class name
 * @return true if the class exists
 */
Nornix.css.contains = function (el, s)
{
	if (!el || !el.className) return false;
	var arr = el.className.split(" "), i = 0, t;
	while (t = arr[i++])
	{
		if (t === s) return true;
	}
	return false;
};

/**
 * Get position of node.
 * @param {HTMLElement} obj the object to work on
 * @return absolute position as .x and .y of return value.
 */
Nornix.css.getPos = function (obj)
{
	var pos = {x: obj.offsetLeft || 0, y: obj.offsetTop || 0};
	while (obj = obj.offsetParent)
	{
		pos.x += obj.offsetLeft || 0;
		pos.y += obj.offsetTop || 0;
	}
	return pos;
};

/**
 * Get current style of element.
 * @function
 * @param {HTMLElement} el element to get the style from
 * @param {String} styleProp CSS style propterty to fetch
 * @return current setting of style property
 */
Nornix.css.getProperty = function (el, styleProp)
{
	if (window.getComputedStyle)
	{
		return function (el, styleProp)
		{
			return window.getComputedStyle(el, "").getPropertyValue(styleProp);
		};
	}
	return function (el, styleProp)
	{
		return el.currentStyle ? el.currentStyle[Nornix.css.prop2Js(styleProp)] : null;
	};
}();

/**
 * Convert CSS property to JS equivalent.
 * @param {String} p property to convert
 * @return JS property
 */
Nornix.css.prop2Js = function (p)
{
	var arr = p.split("-");
	if (arr.length > 1)
	{
		var sum = arr[0], i = 1, part;
		while (part = arr[i++])
		{
			sum += part.charAt(0).toUpperCase() + part.substr(1);
		}
		return sum;
	}
	return p;
};


/**
 * Create static copy of a live collection.
 * This makes it much faster to work with the elements.
 * @param {HTMLCollection} collection a live collection
 * @return static copy of the live collection
 */
Nornix.dom.live2copy = function (collection)
{
	var elements = [], i = 0, el;
	while (el = collection[i++])
	{
		elements[elements.length] = el;
	}
	return elements;
}

/**
 * Function that reads the text content of a DOM node.
 * IE FIX for .textContent
 * @param {Node} node DOM node to get text content from
 * @return text content of node
 * @type String
 */
Nornix.dom.getTextContent = function (node)
{
	if (typeof node.textContent != "undefined")
	{
		return node.textContent;
	}
	else
	{
		return node.innerText;
	}
};

/**
 * Preload image files for faster rendering.
 * @param {Array} imgs array of images
 * @param {String} path path to images
 */
Nornix.dom.imagePreload = function (imgs, path)
{
	var i = 0, img;
	while (img = imgs[i++])
	{
		(new Image()).src = path + img; // no need to keep the images!
	}
};

/**
 * Find first or last children of element type.
 * @param {HTMLElement} nodes node, which children nodes will be searched
 * @param {String} nodeName type of HTML element we are looking for
 * @param {Function} nodeAction closure for action to execute
 * @param {boolean} backwards search from the end of the child list when true
 * @return value of action closure.
 */
Nornix.dom.findChildOfType = function (nodes, nodeName, nodeAction, backwards)
{
	var e;
	if (!backwards)
	{
		var i = 0;
		while (e = nodes.childNodes[i++])
		{
			if (Nornix.dom.eqNodeName(e, nodeName))
			{
				return nodeAction(e);
			}
		}
	}
	else
	{
		var i = nodes.childNodes.length - 1;
		while (e = nodes.childNodes[i--])
		{
			if (Nornix.dom.eqNodeName(e, nodeName))
			{
				return nodeAction(e);
			}
		}
	}
};

/**
 * Check if node name of element is equal to a string.
 * @param {HTMLElement} el the object to work on
 * @param {String} nodeName
 * @return true if the node name is equivalent
 */
Nornix.dom.eqNodeName = function  (el, nodeName)
{
	if (el && el.nodeName && el.nodeName.toLowerCase() === nodeName)
	{
		return true;
	}
	return false;
};

/**
 * String function that removes whitespace from the start and end of a string.
 * @param {String} s the string to use
 * @return {String} the string with whitespace removed
 * @type String
 */
Nornix.util.trim = function (s)
{
	return s.replace(/^\s*|\s*$/g, "");
};

/**
 * True when browser is Internet Exlorer.
 * @type boolean
 */
Nornix.util.isIe = document.all && window.opera === undefined;

