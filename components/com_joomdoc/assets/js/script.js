/**
 * @version $Id$
 * @package Joomla.Administrator
 * @subpackage JoomDOC
 * @author ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

window.addEvent('domready', function() {
	// unpack/pack search box according to value in cookies 
	JoomDOC.setSearchTools();
});

var JoomDOC = {
	renamedElement : null,
	renameOldValue : null,
    
    /**
     * Joomla! 3.3 bootstrap tooltip
     */
    bTip : function(selector) {
        jQuery(selector).each(function(i, e) {
            var p = jQuery(e).attr('title').split('::', 2);
            if (p[0] != undefined && p[1] != undefined) {
                jQuery(e).attr('title', ('<strong>' + p[0] + '</strong><br/>' + p[1]));
            } else {
                jQuery(e).attr('title', ('<strong>' + jQuery(e).attr('title') + '</strong>'));
            }
        });
    },
    
    /**
     * Mootools tooltip
     * @param string id
     * @param string ttl
     * @param string txt
     */
    mTip : function(id, ttl, txt) {
        var el = document.id(id);
        if (el) {
            el.store('tip:title', ttl).store('tip:text', txt);
            new Tips(el, { maxTitleChars: 50, fixed: false});
        }
    },
    
    /**
     * jQuery tooltip
     * @param string id
     * @param string ttl
     * @param string txt
     */    
    jTip : function(id, ttl, txt) {
        jQuery('#' + id)
            .attr('title', ('<strong>' + ttl + '</strong><br/>' + txt))
            .tooltip({'html': true, 'container': 'body'});
    },    
	
	addSymLink : function(data) {
		window.parent.document.adminForm.symlink.value = data;
		window.parent.document.adminForm.task.value = 'symlinks.add';
		window.parent.document.adminForm.submit();
	},

	/**
	 * Open/close search box and reverse unpack/pack box tools.
	 * 
	 * @param open
	 *            string 1/0 - open/close
	 */
	setSearchTools : function(open) {
		// search box
		var search = document.getElementById('searchBox');
		// search box unpack/pack tools
		var openSearch = document.getElementById('openSearch');
		var closeSearch = document.getElementById('closeSearch');
		if (!search || !openSearch || !closeSearch) {
			// all elements required
			return;
		}
		// use function param or cookie value
		var cookie = new Hash.Cookie('joomdoc_search');
		open = open != undefined ? open : cookie.get('joomdoc_search');
		if (open == '1') {
			// open search box and reverse tools
			this.visible(search);
			this.hide(openSearch);
			this.visible(closeSearch);
		} else {
			// close search box and reverse tools
			this.hide(search);
			this.visible(openSearch);
			this.hide(closeSearch);
		}
		// save open status into cookies
		cookie.set('joomdoc_search', open);
	},

	/**
	 * Valid search form before submit.
	 * 
	 * @return boolean
	 */
	searchSubmit : function() {
		document.getElementById('joomdoc_search').value = 1;
		document.adminForm.submit();
		return true;
	},

	/**
	 * Reset search form.
	 */
	resetSubmit : function() {
		$('adminForm').getElements('*[name^=joomdoc_]').each(function(e) {
			e.set('value', ''); // input text 
			e.erase('checked'); // checkbox or radio
			e.set('selected', ''); // select box
			e.getElements('option').each(function(e) { // multi select box
				e.set('selected', '');
			});
		});
		if ($('joomdoc_type'))
			$('joomdoc_type').value = joomDOCsearchDefaultType;
		if ($('joomdoc_ordering'))
			$('joomdoc_ordering').value = joomDOCsearchDefaultOrder;
	},

	/**
	 * Open download button after confirm license.
	 * 
	 * @param toogler
	 *            checkbox to confirm license
	 */
	confirmLicense : function(toogler) {
		var download = document.getElementById('download');
		download.className = toogler.checked ? '' : 'blind';
	},

	/**
	 * Copy path field into title field.
	 */
	copyPath : function(path) {
		document.getElementById('jform_title').value = path;
		return false;
	},

	/**
	 * Hide element. Add class blind with negative absolute position.
	 * 
	 * @param element
	 */
	hide : function(element) {
		this.visible(element);
		element.className = element.className != '' ? (element.className + ' blind')
				: 'blind';
	},

	/**
	 * Confirm URL sending.
	 * 
	 * @param url
	 */
	confirm : function(url) {
		if (confirm(joomDOCmsgAreYouSure)) {
			var token = $('joomdocToken');
			var separator = url.match(/\?/) ? '&' : '?';
			window.location.href = url + separator + 'token=' + token.name;
		}
	},

	/**
	 * Visible element. Remove class blind.
	 * 
	 * @param element
	 */
	visible : function(element) {
		element.className = element.className.replace(/blind/gi, '');
		element.className = element.className.trim();
	},

	/**
	 * Check file/folder checkbox. After checkin standard checkbox (for
	 * document) then check also hidden for file/folder.
	 * 
	 * @param element
	 *            HTML element standard checkbox cb#
	 * @param id
	 *            row ID
	 */
	check : function(element, id) {
            
		var listener = document.getElementById('cbb' + id);               
		listener.checked = element.checked;
                
	},
        
        checkCheckBox : function(element) {
            
            element.checked = true;
            
        },
        
    /**
     * Upload files by drop & drag.
     * 
     * @param {String} upload URL
     */    
    dropAndDrag : function(url, img) {
        var joomdocDropzone = {
            dropzone: new Dropzone('#upload', {
                'url': url, 
                'paramName': 'upload',
                'uploadMultiple': true,
                'previewsContainer': '#preview',
                'accept': function(file, done) {
                    if (JoomDOC.upload(file)) {
                        done();
                    }
                }
            }),
            location: null,
            total: 0
        };
        /**
         * Show ajax preloader with upload start.
         */
        joomdocDropzone.dropzone.on('processing', function() {
            jQuery('#preloader').show();
        });
        /**
         * Complete queue upload done.
         */
        joomdocDropzone.dropzone.on('queuecomplete', function() {
            if (joomdocDropzone.location !== null && joomdocDropzone.total === 1) {
                location.href = joomdocDropzone.location; // with single upload go to document detail
            } else {
                location.reload(); // with multi upload stay on document list
            }
        });  
        /**
         * One batch upload done.
         */
        joomdocDropzone.dropzone.on('successmultiple', function(files, location) {
            joomdocDropzone.total += files.length;
            joomdocDropzone.location = location;
        });
    },

	/**
	 * Upload new file into current folder.
	 * 
	 * @param element
	 *            button to start
	 * @param task
	 *            request task value
	 * @param msgEmpty
	 *            message if file fields is empty (no select file)
	 * @param msgOverwrite
	 *            message if file alreday exists (allow overwrite)
	 * @param msgDirExists
	 *            message if exist directory with the same name
	 * @returns {Boolean} false to disable automatic submit
	 */
	upload : function(element) {
        // name of uploaded file (on windows full path)
        var path = '';
        try {
            var upload = document.getElementById('upload');
            if (upload.value.trim() === '') {
                // no select file to upload
                alert(joomDOCmsgEmpty);
                return false;
            }
            path = upload.value;
		} catch(e) {
            path = element.name;
        }

		// convert backslashes to slashes
		path = path.replace(/\\/g, '/');
		// split to path segments to get file name without path
		path = path.split('/');
		var length = path.length;
		// get file name
		if (length > 0) {
			path = path[length - 1];
		} else if (upload) {
			path = upload.value;
		}
        if (joomDOCcfgConfirmOverwite == '1') {
            // control if file with the same ename already exists in current
            // directory
            for ( var i = 0; i < joomDOCFiles.length; i++) {
                if (joomDOCFiles[i] == path) {
                    // name of some file in directory equals with uploaded
                    if (!confirm(joomDOCmsgOverwrite)) {
                        return false;
                    }
                    break;
                }
            }
        }
		// control if folder with the same ename already exists in current
		// directory
		for ( var i = 0; i < joomDOCFolders.length; i++) {
			if (joomDOCFolders[i] == path) {
				// name of some folder in directory equals with uploaded
				alert(joomDOCmsgDirExists);
				return false;
			}
		}
		// OK set task and submit
        try {
            element.form.task.value = joomDOCTaskUploadFile;
            element.form.submit();
        } catch(e) {
            return true;
        }
	},
	/**
	 * Open rename dialog.
	 * 
	 * @param element
	 */
	openRename : function(element) {
		if (this.renamedElement) {
			this.closeRename(this.renamedElement, this.renameOldValue);
		}
		// cells in table row
		var cell = $$('#openRename' + element).getParent('td');
		cell = cell[0];
		var row = cell.getParent();
		var cells = row.getChildren();
		for ( var k = 0; k < cells.length; k++) {
			// rename dialog contain cell with class filepath
			if (cells[k].className == 'filepath') {
				// hide link to file
				var link = cells[k].getElement('a');
				this.hide(link);
				// visible rename box
				var div = cells[k].getElement('div');
				this.visible(div);                               
				// safe renamed element to close if user will click on next
				// rename tool
				var children = div.getChildren();
				this.renameOldValue = link.innerHTML;
				this.renamedElement = children[0];
				// next step
				continue;
			}
			// hide rename start button
			if (cells[k].className == 'rename') {
				var links = cells[k].getChildren();
				this.hide(links[0]);
				// stop all is satisfied
				break;
			}
		}
	},

	/**
	 * Close rename dialog.
	 * 
	 * @param element
	 *            cancel button to get position
	 * @param oldValue
	 *            old file/folder name
	 * @returns {Boolean} false to disable automatic submit
	 */
	closeRename : function(element, oldValue) {
		// div containing rename tools
		var div = element.getParent();
		// table cell
		var cell = div.getParent();
		// input to new name
		var input = div.getElement('input');
		// reset to old value
		input.value = oldValue;
		// visible file downlad link
		this.visible(cell.getElement('a'));
		// hide rename tools
		this.hide(cell.getElement('div'));
		// file/folder table row
		var row = cell.getParent();
		var cells = row.getChildren();
		for ( var k = 0; k < cells.length; k++) {
			// visible rename start button
			if (cells[k].className == 'rename') {
				var links = cells[k].getChildren();
				this.visible(links[0]);
				// stop all is satisfied
				return false;
			}
		}
		return false;
	},
	/**
	 * Rename file/folder
	 * 
	 * @param element
	 *            start to button
	 * @param task
	 *            request task value
	 * @param oldName
	 *            old file name
	 * @param path
	 *            relative file path
	 * @param msgSameName
	 *            message if in rename input is the same name as is old file
	 *            name
	 * @param msgEmptyName
	 *            message if name is empty
	 * @param msgFileExists
	 *            message if current folder already exists file with the sane
	 *            name
	 * @param msgDirExists
	 *            message if current folder already exists subfolder with the
	 *            sane name
	 * @returns {Boolean} false to disable automatic submit
	 */
	rename : function(element, task, oldName, path, msgSameName, msgEmptyName,
			msgFileExists, msgDirExists) {
		// parent of input and button
		var parent = element.getParent('td');
		// input with new name
		var newName = parent.getElement('input');
		// new name and old name cannot be the same
		if (newName.value.trim() == oldName.trim()) {
			alert(msgSameName);
			return false;
		}
		// new name cannot be empty
		if (newName.value.trim() == '') {
			alert(msgEmptyName);
			return false;
		}
		// unable rename to exists file
		for ( var i = 0; i < joomDOCFiles.length; i++) {
			if (joomDOCFiles[i] == newName.value) {
				alert(msgFileExists);
				return false;
			}
		}
		// unable rename to exists folder
		for ( var i = 0; i < joomDOCFolders.length; i++) {
			if (joomDOCFolders[i] == newName.value) {
				alert(msgDirExists);
				return false;
			}
		}
		// add values into form hidden fields
		element.form.task.value = task;
		element.form.renamePath.value = path;
		element.form.newName.value = newName.value;
		// submit

		element.form.submit();
	},
	/**
	 * Create subfolder in current folder.
	 * 
	 * @param element
	 *            start button to acces form
	 * @param task
	 *            request task value
	 * @param msgEmpty
	 *            message if name is empty
	 * @param msgFileExists
	 *            message if in current folder already exist file with the same
	 *            name
	 * @param msgDirExists
	 *            message if in current folder already exist folder with the
	 *            same name
	 * @returns {Boolean} false to disable automatic submit
	 */
	mkdir : function(element, canCreate) {
		var name = document.getElementById('newfolder');
		if (name.value.trim() == '') {
			// subfolder name is empty
			alert(joomDOCmsgMkdirEmpty);
			return false;
		}
		// control if current folder already exist file with the same name
		for ( var i = 0; i < joomDOCFiles.length; i++) {
			if (joomDOCFiles[i] == name.value) {
				/*
				 * name of some file in current folder equals with new subfolder
				 * name
				 */
				alert(joomDOCmsgMkdirFileExists);
				return false;
			}
		}
		// control if current folder already exist subfolder with the same name
		for ( var i = 0; i < joomDOCFolders.length; i++) {
			if (joomDOCFolders[i] == name.value) {
				/*
				 * name of some subfolder in current folder equals with new
				 * subfolder name
				 */
				alert(joomDOCmsgMkdirDirExists);
				return false;
			}
		}
		// OK set task and submit
		element.form.task.value = joomDOCmsgMkdirTask;
        if (canCreate && confirm(joomDOCmsgMkdirCreateDocument)) {
            element.form.doccreate.value = 1;
        } else {
            element.form.doccreate.value = 0;
        }
        element.form.submit();		             
	}/* <PAID> */,

	/**
	 * Start webdav editing.
     * @param {int} num number of tries
	 */
	webdavInit : function(num) {
		var filelist = $$('.webDAVHandler'); // file list on the page
		var handlers = $$('.fileList_table .editWebDav'); // SabreDAV edit handlers
        var names = this.getCleanValues(filelist);
		var links = this.getCleanValues(handlers);
        if (filelist.length > 0 && links.length === 0) { // SabreDAV not initialised yet
            num = num === undefined ? 0 : (num + 1);
            if (num < 3) { // max. 3 tries
                return setTimeout('JoomDOC.webdavInit(' + num + ')', 1000); // wait and try to init again
            }
        }
		for ( var i = 0; i < names.length; i++) {
            filelist[i].set('html', '');
			for ( var j = 0; j < links.length; j++) {
				if (names[i] === links[j]) {
                    filelist[i].adopt(handlers[j]); // show handler on page
					break;
				}
			}
		}
	},
	/**
	 * Get values cleanup from tags span and strong.
	 * 
	 * @param items
	 * @returns {Array}
	 */
	getCleanValues : function(items) {
		var itemsValues = new Array();
		for ( var i = 0; i < items.length; i++) {
			var html = items[i].innerHTML;
			// strip tag strong leave content
			html = html.replace(/<\/?strong[^>]*>/gi, '');
			// strip tag span with content
			html = html.replace(/<span[^>]*>[^<]*<\/span>/gi, '');
			itemsValues[i] = html;
		}
		return itemsValues;
	},
	
	reindex : function(option, task) {
		try { // mootools 1.2
			new Request({
				url: 'index.php',
				method: 'get',
				data: {'option' : option, 'task': task, 'tmpl': 'component'},
				async: false,
				onSuccess: function(responseText) {
					var output = JSON.decode(responseText);
					$('system-message-container').set('html', output.message);
					if (output.status == 'CONTINUE') {
						JoomDOC.reindex(option, task);
					}
				}
			}).send();
		} catch(e) { // mootools 1.1
			new Json.Remote('index.php?option=' + option + '&task=' + task, {
				async: false,
				onComplete: function(jsonObj) {
					if (!$('joomdoc-message')) {
						var div = new Element('div', {id: 'joomdoc-message'});
						div.injectAfter('submenu-box');
					}
					$('joomdoc-message').setHTML(jsonObj.message);
					if (jsonObj.status == 'CONTINUE')
						JoomDOC.reindex(option, task);
				}
			}).send();
		}
	}    
	/* </PAID> */
}