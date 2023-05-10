/**
 * The Nornix.TreeMenu class creates an explorer-like interface from nested lists.
 * The menu can use either server-side facilities to create classes for
 * the styling with CSS to hook on to.
 *
 * Only set inAllowWhitespace to true when necessary, as it slows down the script.
 *
 * Usage instructions at http://treemenu.nornix.com/info/usage
 *
 * @author      Anders Nawroth <http://www.anders.nawroth.com/>
 * @license     LGPL http://treemenu.nornix.com/license
 * @copyright   2006--2008
 * @version     2.2.0 (2008-03-11)
 * @constructor
 * @param       {String} inMenuId id of the element surrounding the menu
 * @param       {boolean} inAllowWhitespace if true, whitespace is allowed between menu elements in the HTML code
 */
Nornix.TreeMenu = function (inMenuId, inAllowWhitespace)
{
	/**
	 * Variable to work around ECMAScript problems with the "this" keyword inside functions.
	 * @type Object
	 * @private
	 */
	var self = this;
	/**
	 * Id of current menu wrapper.
	 * @type String
	 * @private
	 */
	var menuId = inMenuId ? inMenuId : "menu";
	/**
	 * Name of cookie, to avoid namespace-clashes between multiple menus.
	 * @type String
	 * @private
	 */
	var cookieName = "tree" + menuId;
	/**
	 * Cookie with saved node status information.
	 * @type String
	 * @private
	 */
	var oldTree = Nornix.cookies.read(cookieName);
	/**
	 * Allow whitespace in menu (true) or not (false).
	 * @type boolean
	 * @private
	 */
	var allowWhitespace = (inAllowWhitespace === false) ? false : true;
	/**
	 * Empty location for links, to compare with.
	 * @type String
	 * @private
	 */
	var emptyHref = window.location+"#";
	/**
	 * The current marked item in the menu.
	 * This has to be remebered for dynamic usage of the menu.
	 * @type HTMLAElement
	 * @private
	 */
	var currentItem = null;
	/**
	 * The href attribute that was removed from the current marked item in the menu.
	 * This has to be remebered for dynamic usage of the menu.
	 * @type String
	 * @private
	 */
	var currentItemHref = null;
	/**
	 * RegExp to find the class "open" in strings.
	 * @type RegExp
	 * @private
	 * @final
	 */
	self.openPattern = /(^| )open( |$)/;
	/**
	 * Open all folders in the menu.
	 * @function
	 */
	this.openAll = openAll;
	/**
	 * Close all folders in the menu.
	 * @function
	 */
	this.closeAll = closeAll;

	/**
	 * Start the tree menu.
	 * Initialization will be called as soon as the menu object is available in the DOM.
	 * Images will be preloaded while the DOM is built.
	 */
	this.start = function()
	{
		Nornix.events.delayedInit(menuId, init);
		if (self.config.preloadImages && !Nornix.cookies.read("preImg"))
		{
			Nornix.dom.imagePreload(self.config.preloadImages, self.config.imagePath);
			Nornix.cookies.create("preImg", "x");
		}
	};

	/**
	 * Initialize the menu.
	 * @private
	 */
	function init (menu)
	{
		if (!document.getElementById || !document.createElement) return;

		self.menu = menu;
		self.menuElements = Nornix.dom.live2copy(self.menu.getElementsByTagName("li"));

		if (self.config.openCloseAll)
		{
			createOpenCloseAllIcons();
		}

		// set classes, if not set in the HTML, and prepare folders
		setClasses();

		// re-render menu now if IE
		ieFix();

		// set up event handling now
		EventHandlers();
	}

	/**
	 * Initialize the event handlers for click and keyboard events.
	 * @constructor
	 * @private
	 */
	function EventHandlers()
	{
		init();

		/**
		 * Initialize event handlers.
		 */
		function init ()
		{
			// add click handler to menu link.
			if (self.config.menuLinkElement)
			{
				Nornix.events.add(document.getElementById(self.config.menuLinkElement), 'click', menuJump);
				if (Nornix.util.isIe)
				{
					Nornix.events.add(document.getElementById(self.config.menuLinkElement), 'focus', menuJumpIe);
				}
			}

			// if whitespace was allowed, remove it and change the setting to reflect the new state
			if (allowWhitespace)
			{
				removeWhitespace(self.menu);
				allowWhitespace = false;
			}

			// add event handlers to menu
			Nornix.events.add(self.menu, 'click', checkClickDynamic, true);
			Nornix.events.add(self.menu, 'keydown', checkKeyDynamic, true);

			// add unload handler to save cookie
			Nornix.events.add(window, "unload", save);
		}

		/**
		 * Move focus to the root element of the menu.
		 * Used as an event handler.
		 * @private
		 * @member EventHandlers
		 * @param {Event} e event object
		 * @return {boolean} always returns false
		 */
		function menuJump (e)
		{
			focusNode(self.menu);
			Nornix.events.cancel(e);
			return false;
		}

		/**
		 * Move focus from the menu link to the menu itself in Internet Exlorer.
		 * Used as an event handler.
		 * @private
		 * @param {Event} e event object
		 */
		function menuJumpIe (e)
		{
			if (e.altKey)
			{
				focusNode(self.menu);
			}
		}

		/**
		 * Handle all click events.
		 * @private
		 * @param {Event} e event object
		 * @return {boolean} false when handling the event
		 */
		function checkClickDynamic (e)
		{
			var t = e.target;
			switch (t.className)
			{
				case "closeTree":
					closeAll();
					return false;
				case "openTree":
					openAll();
					return false;
			}
			var p = t.parentNode;
			if (!p) return true; // we can't handle this
			if (Nornix.dom.eqNodeName(t, "span"))
			{
				toggle(p);
				return;
			}
			// change current marker?
			if (!isHrefEmpty(t))
			{
				changeCurrentItem(t);
			}
			// run click handlers and toggle folders if emtpty href
			if (isFolder(p))
			{
				// p is a folder
				if (self.hooks.dynamicFolderLinks && !self.hooks.dynamicFolderLinks(t))
				{
					Nornix.events.cancel(e);
					return false;
				}
				if (isHrefEmpty(t))
				{
					toggle(p);
					Nornix.events.cancel(e);
					return false;
				}
			}
			else
			{
				// p is not a folder
				if (self.hooks.dynamicDocumentLinks && !self.hooks.dynamicDocumentLinks(t))
				{
					Nornix.events.cancel(e);
					return false;
				}
			}
			return true; // not found
		}

		/**
		 * Handle all keydown events.
		 * Looks for lots of different key strokes.
		 * @private
		 * @param {Event} e event object
		 * @return {boolean} true if the event action should continue.
		 */
		function checkKeyDynamic(e)
		{
			var isRoot, isFolder, isDocument, isCloser, isOpener, isDocOrFolder, isCloserOpener,
				o = e.target, p = o.parentNode, node;
			switch (true)
			{
				case Nornix.css.contains(o, "root"): isRoot = true; break;
				case Nornix.css.contains(o, "closeTree"): isCloser = true; isCloserOpener = true; break;
				case Nornix.css.contains(o, "openTree"): isOpener = true; isCloserOpener = true; break;
				case Nornix.css.contains(p, "folder"): isFolder = true; isDocOrFolder = true; break;
				case Nornix.css.contains(p, "document"): isDocument = true; isDocOrFolder = true; break;
				default: return true; // not found;
			}

			var keyCode = e.keyCode !== null ? e.keyCode : e.which;
			if (keyCode === 56) // ( 8
			{
				openAll();
			}
			else if (keyCode === 57) // 9 )
			{
				closeAll(o);
				if (!o.offsetParent)
				{
					Nornix.dom.findChildOfType(
						self.menu, "a", function (a)
						{
							focusAnchor(a);
						}
					);
				}
			}
			else if (isFolder && (keyCode == 32 || (keyCode == 13 && isHrefEmpty(o))))
			{
				toggle(p);
			}
			else if (isRoot &&
			 (keyCode === 40 || ((keyCode === 39) && !focusAnchor(o.nextSibling))) &&
			  (node = p.lastChild.firstChild)) // arrow down or right
			{
				focusNode(node);
			}
			else if (isDocOrFolder && (keyCode === 40)) // arrow down
			{
				if (isDocument || !isOpen(p))
				{
					if (node = p.nextSibling) focusNode(node);
					else if (isFolder && (node = o.nextSibling.firstChild))
					{
						// open folder when moving down from it
						toggle(p);
						focusNode(node);
					}
					else
					{
						// find next available node
						node = p;
						while (node && node !== self.menu)
						{
							node = node.parentNode.parentNode;
							if (node.nextSibling)
							{
								focusNode(node.nextSibling);
								break;
							}
						}
					}
				}
				else
				{
					if (node = p.nextSibling) focusNode(node);
					else if (node = o.nextSibling.firstChild) focusNode(node);
				}
			}
			else if (isDocOrFolder && (keyCode === 38)) // arrow up
			{
				if (node = p.previousSibling) focusNode(node);
				else if (node = p.parentNode.parentNode) focusNode(node);
			}
			else if (isDocOrFolder && (keyCode === 39)) // arrow right
			{
				if (isDocument && (node = p.nextSibling)) focusNode(node);
				else if (o.nextSibling && (node = o.nextSibling.firstChild))
				{
					if (isOpen(p)) focusNode(node);
					else
					{
						toggle(p);
						focusNode(node);
					}
				}
			}
			else if (isDocOrFolder && (keyCode === 37)) // arrow left
			{
				if (node = p.parentNode.parentNode) focusNode(node);
				if (isFolder && isOpen(p)) toggle(p);
			}
			else if (isDocument && (keyCode === 32)) {} // SPACE
			else if (keyCode === 27 && self.config.contentElement) // ESC
			{
				window.location.hash = self.config.contentElement;
			}
			else if (isCloserOpener && (keyCode === 37 || keyCode === 38)) // arrow left or up
			{
				focusAnchor(o.previousSibling);
			}
			else if (isCloser && (keyCode === 39 || keyCode === 40)) // arrow right or down
			{
				focusAnchor(o.nextSibling);
			}
			else if (isOpener && (keyCode === 39 || keyCode === 40)) // arrow right or down
			{
				focusNode(o.nextSibling.firstChild);
			}
			else
			{
				return true;
			}
			Nornix.events.cancel(e);
			return false;
		}
	}

	/**
	 * Set classes to display tree menu.
	 * Dynamically set classes on menu items, when not set in the HTML code.
	 * Uses the classes folder/document/open/closed/last.
	 * Make folders from the current element/page and "upwards" open.
	 * Prepare tree from stored state in cookie.
	 * Adds span elements inside all li elements.
	 * Adds event handlers on menu items.
	 * Creates the self.menuFolders array of all menu folders.
	 * @private
	 * @requires Nornix.cookies Uses the Nornix.cookies.read() function.
	 */
	function setClasses ()
	{
		/**
		 * Check if Anchor element has no href attribute, in that case, set it to "javascript:;".
		 * @private
		 * @param {HTMLAnchorElement} a anchor element to test/change
		 */
		function setEmptyHrefAsCurrent (a)
		{
			if (self.config.dynamicClasses)
			{
				currentItemHref = a.href;
				a.removeAttribute("href");
			}
			if (!a.href)
			{
				// the current node has no href attribute
				currentItem = a;
				a.href = "javascript:;";
				makeCurrent(a);
			}
		}

		var folders = [];
		var i, li, a, itemIsFolder, chr;
		var menuElements = self.menuElements;
		// setup
		var orgSpan = document.createElement("span");
		var span;
		orgSpan.title = self.texts.openFolderTitle; // default value is closed folder
		// setup list of folder elements
		var iFolder = 0;

		if (self.config.dynamicClasses)
		{
			var page = window.location.href;
			// set up root node
			// - find an <a> element that is a child of the menu element
			Nornix.dom.findChildOfType(
				self.menu, "a", function (a)
				{
					Nornix.css.swap(a, null, "root");
					if (a.href === page)
					{
						setEmptyHrefAsCurrent(a);
					}
				}
			);

			// loop list items
			i = 0;
			while (li = menuElements[i++])
			{
				a = li.firstChild;
				itemIsFolder = isFolder(li);
				var className;
				if (itemIsFolder)
				{
					className = "folder closed"; // default is closed
					folders[folders.length] = li;
					span = orgSpan.cloneNode(false);
					li.insertBefore(span, a);
					chr = oldTree.charAt(iFolder++);
					if (chr && chr === "-")
					{
						className = "folder open";
						li.firstChild.title = self.texts.closeFolderTitle;
					}
				}
				else
				{
					className = "document"; // only one childNode (a link)
				}
				if (allowWhitespace)
				{
					Nornix.dom.findChildOfType(
						li.parentNode, "li",
						function (last)
						{
							if (li === last)
							{
								className += " last";
							}
						},
						true // search backwards
					);
				}
				else
				{
					if (li === li.parentNode.lastChild)
					{
						className += " last";
					}
				}
				if (a.href == page)
				{
					// current page
					setEmptyHrefAsCurrent(a);
					if (itemIsFolder) // only open if li is folder
					{
						className = "folder open";
						li.firstChild.title = self.texts.closeFolderTitle;
					}
					// trace upwards in the tree to open the "path" to the current page
					var node = li.parentNode.parentNode;
					while (node && node != self.menu)
					{
						makeOpen(node);
						node = node.parentNode.parentNode;
					}
				}
				li.className = className;
			}
		}
		else
		{
			i = 0;
			while (li = menuElements[i++])
			{
				a = li.firstChild;
				itemIsFolder = isFolder(li);
				// current item?
				if (isHrefEmpty(a))
				{
					setEmptyHrefAsCurrent(a);
				}
				if (itemIsFolder)
				{
					folders[folders.length] = li;
					// open/close folders with the space bar or enter key or mouse click
					span = orgSpan.cloneNode(false);
					li.insertBefore(span, a);
					chr = oldTree.charAt(iFolder++);
					if (chr && chr === "-")
					{
						makeOpen(li);
					}
				}
			}
			// is root node also current?
			i = 0;
			while (a = self.menu.childNodes[i++])
			{
				if (Nornix.dom.eqNodeName(a, "a"))
				{
					setEmptyHrefAsCurrent(a);
					break;
				}
			}
		}
		self.menuFolders = folders;
	}

	/**
	 * Move "current" status to another document node.
	 * Also remove status on old current node.
	 * @private
	 * @param {HTMLAnchorElement} newCurrent anchor element to change
	 */
	function changeCurrentItem (newCurrent)
	{
		// remove current status on old current item
		if (currentItem)
		{
			makeNotCurrent(currentItem);
		}
		makeCurrent(newCurrent);
		currentItem = newCurrent;
	}
	
	/**
	 * Make a link the current menu item.
	 * @private
	 * @param {HTMLAnchorElement} a anchor element to make the current choice
	 */
	function makeCurrent (a)
	{
		Nornix.css.swap(a, null, "current");
		if (self.config.markCurrentItem)
		{
			a.insertBefore(document.createElement("span"), a.firstChild);
		}
	}

	/**
	 * Make a link not the current menu item any more.
	 * @private
	 * @param {HTMLAnchorElement} a anchor element to make not the current choice
	 */
	function makeNotCurrent (a)
	{
		if (currentItemHref)
		{
			currentItem.href = currentItemHref; // restore href
			currentItemHref = null; // only needed once
		}
		Nornix.css.swap(currentItem, "current", null);
		if (self.config.markCurrentItem)
		{
			currentItem.removeChild(currentItem.firstChild);
		}
	}


	/**
	 * Method that saves the current tree state in a cookie.
	 * @requires Nornix Uses the Nornix.cookies.create() function.
	 * @private
	 */
	function save()
	{
		var s = "", i = 0, li;
		var menuFolders = self.menuFolders;
		while (li = menuFolders[i++])
		{
			if (isOpen(li))
			{
				s += "-";
			}
			else
			{
				s += "+";
			}
		}
		Nornix.cookies.create(cookieName, s);
	}

	/**
	 * Check if a link is emtpy or "fake-empty".
	 * "#" or "javascript:;" are regarded as "fake-empty".
	 * @param {HTMLAnchorElement} node HTML a element
	 * @return {boolean} true if the link is empty or "fake-empty"
	 * @private
	 */
	function isHrefEmpty(node)
	{
		if (node.href && (node.href == emptyHref || node.href === "javascript:;"))
		{
			return true;
		}
		return !node.href;
	}

	/**
	 * IE bugfix, forces IE to "re-render" the menu
	 * and sets focus on elements that have fired an event
	 * @private
	 */
	function ieFix() {}
	if (Nornix.util.isIe)
	{
		ieFix = function()
		{
 			self.menu.style.position = "absolute";
 			self.menu.style.position = "relative";
			try
			{
				window.event.srcElement.focus();
			}
			catch (err) {}
		};
	}
	else
	{
		ieFix = function(){};
	}

	/**
	 * Remove whitespace from node and children.
	 * One level of unrolling the recursive call seems
	 * to be the optimal choice in IE6 (which is slow in this case).
	 * @param {HTMLElement} n HTML element
	 * @private
	 */
	function removeWhitespace(n)
	{
		var i = 0, c;
		while (c = n.childNodes[i])
		{
			switch (c.nodeType)
			{
				case 1:
					var j = 0, c2;
					while (c2 = c.childNodes[j])
					{
						switch (c2.nodeType)
						{
							case 1: // element node
								removeWhitespace(c2);
								break;
							case 3: // text node
								if (!/\S/.test(c2.nodeValue))
								{
									c.removeChild(c2);
									continue;
								}
								break;
							case 8: // comments and empty textnodes
								c.removeChild(c2);
								continue;
						}
						j++;
					}
					break;
				case 3:
					if (!/\S/.test(c.nodeValue))
					{
						n.removeChild(c);
						continue;
					}
					break;
				case 8:
					n.removeChild(c);
					continue;
			}
			i++; // don't move this, the deleting of nodes depends on the ++ not being run!
		}
	}

	/**
	 * Check if a li element is representng a folder in the menu.
	 * Works different when  allowing or not allowing whitespace in the menu.
	 * @param {HTMLLIElement} node HTML li element
	 * @return {boolean} true if the li element is a folder in the menu
	 * @private
	 */
	function isFolder (li)
	{
		if (allowWhitespace)
		{
			return Nornix.dom.findChildOfType(li, "ul", function (x) {return true;});
		}
		else
		{
			return li.childNodes.length > 1;
		}
	}

	/**
	 * Toggle a folder in the menu.
	 * @param {HTMLLIElement} node HTML li element
	 * @private
	 */
	function toggle(node)
	{
		if (!isOpen(node))
		{
			makeOpen(node);
		}
		else
		{
			makeClosed(node);
		}
		ieFix();
	}

	/**
	 * Make a folder in the menu open.
	 * @param {HTMLLIElement} li HTML li element
	 * @private
	 */
	function makeOpen(li)
	{
		Nornix.css.swap(li, "closed", "open");
		li.firstChild.title = self.texts.closeFolderTitle;
	}

	/**
	 * Make a folder in the menu closed.
	 * @param {HTMLLIElement} li HTML li element
	 * @private
	 */
	function makeClosed(li)
	{
		Nornix.css.swap(li, "open", "closed");
		li.firstChild.title = self.texts.openFolderTitle;
	}

	/**
	 * Close all folders.
	 * @private
	 */
	function closeAll()
	{
		var i = 0, menuFolders = self.menuFolders;
		while (li = menuFolders[i++])
		{
			makeClosed(li);
		}
		ieFix();
	}
	
	/**
	 * Open all folders.
	 * @private
	 */
	function openAll()
	{
		var i = 0, menuFolders = self.menuFolders;
		while (li = menuFolders[i++])
		{
			makeOpen(li);
		}
		ieFix();
	}

	/**
	 * Move focus to the a element in this LI or DIV element.
	 * @param {HTMLLIElement} node HTML li element (or other element)
	 * @return {boolean} true if the move was successful.
	 * @private
	 */
	function focusNode (node)
	{
		if (!node || !node.firstChild) return false;
		var n = node.firstChild;
		if (focusAnchor(n)) return true;
		if (!n.nextSibling || node === self.menu) return false;
		n = n.nextSibling;
		return focusAnchor(n);
	}

	/**
	 * Move focus to the a element sent.
	 * @param {HTMLAnchorElement} a HTML A element
	 * @return {boolean} true if the move of focus was successful.
	 * @private
	 */
	function focusAnchor (a)
	{
		if (Nornix.dom.eqNodeName(a, "a"))
		{
			a.focus();
			return true;
		}
		return false;
	}

	/**
	 * Check if folder is open.
	 * @param {HTMLLIElement} node HTML li element
	 * @return {boolean} true if the folder is open
	 * @private
	 */
	function isOpen(li)
	{
		return li.className.search(self.openPattern) !== -1;
	}

	/**
	 * Add icons as HTML a elements for opening and closing all icons.
	 * @private
	 */
	function createOpenCloseAllIcons()
	{
		var orgA = document.createElement("a");
		orgA.href = "javascript:;";
		var a = orgA.cloneNode(false); // new <a> tag
		a.className = "closeTree";
		a.title = self.texts.closeTreeTitle;
		self.menu.insertBefore(a, self.menu.firstChild.nextSibling);
		a = orgA.cloneNode(false); // new <a> tag
		a.className = "openTree";
		a.title = self.texts.openTreeTitle;
		self.menu.insertBefore(a, self.menu.firstChild.nextSibling.nextSibling);
	}
}

/**
 * Configuration of the menu.
 * @member Nornix.TreeMenu
 */
Nornix.TreeMenu.prototype.config =
{
	/**
	 * Setting to turn dynamic class population on/off.
	 * Set to true to generate the menu classes dynamically.
	 * @type boolean
	 */
	"dynamicClasses" : true,
	/**
	 * Setting to add icons for open/close all icons.
	 * Set to true to generate open/close icons.
	 * @type boolean
	 */
	"openCloseAll" : true,
	/**
	 * This is a setting to add an empty span element in the beginning
	 * of the anchor element representing the current page.
	 * Set to false to avoid this action.
	 * @type boolean
	 */
	"markCurrentItem" : false,
	/**
	 * Setting to show where the page content is.
	 * Set to false to disable, or a string to point
	 * to the main content of the page.
	 * This value is where the focus will go when
	 * the user hits the ESC button when a menu
	 * element is active.
	 * @type String
	 */
	"contentElement" : false,
	/**
	 * Setting for link to the menu.
	 * Set to id of the link to the menu, to make it really
	 * focus the root link of the menu, and prevent scrolling.
	 * Set to false to disable this function.
	 * @type String
	 */
	"menuLinkElement" : false,
	/**
	 * List of images to preload.
	 * Be careful with the order of the images!
	 * @type Array
	 */
	"preloadImages" :
		[
			"home-icon.png",
			"close-icon.png",
			"open-icon.png",
			"plus-node.png",
			"minus-node.png",
			"folder-closed-icon.png",
			"doc-node-icon.png",
			"folder-open-icon.png",
			"treemenu-line.png",
			"treemenu-current.png"
		],
	/**
	 * Path to the images used in the menu.
	 * Used for preloading the images.
	 * @type String
	 */
	"imagePath" : "/style/nornix-"
};

/**
 * Extension points to add your own hooks inside the tree menu.
 * @member Nornix.TreeMenu
 */
Nornix.TreeMenu.prototype.hooks =
{
	/**
	 * Function to handle document link clicks in a dynamic way.
	 * The function can do whatever you need to do with the 
	 * clicked document link as a starting point.
	 * The function will be called on all document link clicks,
	 * and is responsible for sorting out what to do by itself.
	 * @type Function
	 * @public
	 * @param {HTMLAnchorElement} a anchor element that was clicked
	 * @return {boolean} false to stop further handling of the click, true to let it continue
	 */
	"dynamicDocumentLinks" : false,
	/**
	 * Function to handle folder link clicks in a dynamic way.
	 * The function can do whatever you need to do with the 
	 * clicked folder link as a starting point.
	 * The function will be called on all folder link clicks,
	 * and is responsible for sorting out what to do by itself.
	 * @type Function
	 * @public
	 * @param {HTMLAnchorElement} a anchor element that was clicked
	 * @return {boolean} false to stop further handling of the click, true to let it continue
	 */
	"dynamicFolderLinks" : false
};

/**
 * Texts used by the tree menu.
 * @member Nornix.TreeMenu
 */
Nornix.TreeMenu.prototype.texts =
{
	/**
	 * Text for "close all" title attribute.
	 * @type String
	 */
	"closeTreeTitle" : "close all folders",
	/**
	 * Text for "open all" title attribute.
	 * @type String
	 */
	"openTreeTitle" : "open all folders",
	/**
	 * Text for "close folder" title attribute.
	 * @type String
	 */
	"closeFolderTitle" : "close folder",
	/**
	 * Text for "open folder" title attribute.
	 * @type String
	 */
	"openFolderTitle" : "open folder"
};


// start the script


