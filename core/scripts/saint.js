$(document).ready(function() {
	$('.saint-admin-overlay').addClass("contracted");
	$('.saint-admin-options.page-options').removeClass("hidden");
	$('.focus').focus();
	
	$.ajaxSetup({
		type: 'POST',
		timeout: 5000,
		cache: false
	});
	
	var Saint = {};
	Saint.connections = new Array();
	Saint.errors = new Array();
	Saint.editing = false;
	Saint.fileManagerIsOpen = false;
	Saint.shopManagerIsOpen = false;
	Saint.addBoxId = 0;
	Saint.wysiwygTarget = '';
	Saint.sfmcurpage = 0;
	Saint.sfmFileToEdit = 0; // File id being edited
	Saint.sfmLabelToEdit = 0; // Label id being edited
	Saint.sfmImageToLoad = 0; // Image to be autoloaded when manager is opened
	Saint.sfmCurrentlyEditing = 0; // The currently selected file element
	Saint.sfmAnimationComplete = true;
	Saint.sflHeight = 0;
	Saint.sflWidth = 0;
	
	/* START Label Editor */
	
	// Flag which editor view is active.
	Saint.sleWysiwygActive = true;
	
	// Interval ID for resizing editor to match textarea while being dragged.
	Saint.sleResizeTimer = 0;
	
	// Currently loaded revision.
	Saint.sleActiveRevision = 0;
	
	// Number of saved revisions.
	Saint.sleNumRevisions = 0;
	
	/**
	 * Start label editor.
	 */
	$(document).on({
		'click': function(event) {
			if (Saint.editing) {
				target = this;
				while (!$(target).hasClass('saint-label') && $(target).prop("tagName") != "BODY") {
					target = $(target).parent();
				}
				if ($(this).hasClass('editing')) {
					event.stopPropagation();
				} else {
					Saint.sleStart($(target));
				}
				return false;
			} else {
				return true;
			}
		}
	},'body:not(.sle-active) div.saint-label.editable, body:not(.sle-active) div.saint-label.editable *');
	
	/**
	 * Stop label editor.
	 */
	$(document).on({
		'keyup': function(event) {
			if (event.which == 27) {
				Saint.sleStop();
				return false;
			}
		}
	},'body.sle-active');
	
	/**
	 * Activate rich text editor.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleToggleWysiwyg();
			return false;
		}
	},'.sle.active .source .toolbar .link.switch');
	
	/**
	 * Activate source editor.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleToggleSource();
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.switch');
	
	/**
	 * Save active label.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleSave($('.sle.active').parent());
			return false;
		}
	},'.sle.active .toolbar .link.save');
	
	/**
	 * Close active label.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleStop($('.sle.active').parent());
			return false;
		}
	},'.sle.active .toolbar .link.close-button');
	
	/**
	 * Bold selected text.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('bold',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.bold');
	
	/**
	 * Italicize selected text.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('italic',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.italic');
	
	/**
	 * Underline selected text.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('underline',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.underline');
	
	/**
	 * Insert link.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('createlink',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.a');
	
	/**
	 * Insert unordered list.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('insertunorderedlist',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.ul');
	
	/**
	 * Insert ordered list.
	 */
	$(document).on({
		'click': function(event) {
			event.preventDefault();
			Saint.sleExecute('insertorderedlist',null);
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.ol');
	
	/**
	 * Change heading size.
	 */
	$(document).on({
		'change': function(event) {
			event.preventDefault();
			if ($(this).attr('value') != "none") {
				Saint.sleExecute('formatBlock','<'+$(this).attr('value')+'>');
				$(this).parent().find('option[value=none]').attr('selected','selected');
			}
			return false;
		}
	},'.sle.active .wysiwyg .toolbar .link.heading');
	
	/**
	 * Change active revision.
	 */
	$(document).on({
		'change': function(event) {
			event.preventDefault();
			if ($(this).attr('value') != Saint.sleActiveRevision && $(this).attr('value') != "load") {
				Saint.sleGetRevision($(this).attr('value'));
			}
			return false;
		}
	},'.sle.active .toolbar .link.revision');
		
	/**
	 * Hotkeys for rich text editor.
	 */
	$(document).on({
		'keypress': function(event) {
		    if (event.ctrlKey && (event.which == 98 || event.which == 66)) {
			    Saint.sleExecute('bold',null);
			    event.preventDefault();
			    return false;
		    }
		    if (event.ctrlKey && (event.which == 105 || event.which == 73)) {
			    Saint.sleExecute('italic',null);
			    event.preventDefault();
			    return false;
		    }
		    if (event.ctrlKey && (event.which == 117 || event.which == 85)) {
			    Saint.sleExecute('underline',null);
			    event.preventDefault();
			    return false;
		    }
		    return true;
		}
	},'.sle.active .wysiwyg');
	
	/**
	 * Hotkeys for both editors.
	 */
	$(document).on({
		'keypress': function(event) {
		    if (event.ctrlKey && (event.which == 115 || event.which == 83)) {
			    Saint.sleSave($('.sle.active').parent());
			    event.preventDefault();
			    return false;
		    }
		    return true;
		}
	},'.sle.active');
	
	/**
	 * Automatically resize editor frame when textarea size is changed.
	 */
	$(document).on({
		'mousedown': function(event) {
			Saint.sleResizeTimer = self.setInterval(function() { Saint.sleResizeSourceFrame() },200);
			return true;
		},
		'mouseup': function(event) {
			clearInterval(Saint.sleResizeTimer);
			Saint.sleResizeSourceFrame();
			return true;
		}
	},'.sle.active .source textarea');
	
	Saint.sleResizeSourceFrame = function() {
		var textarea = $('.sle.active .source textarea');
		var width = textarea.width();
		var height = textarea.height();
		$('.sle.active').width(width+4).height(height+24);
	}
	
	Saint.sleResizeSourceEditor = function() {
		var frame = $('.sle.active');
		var width = frame.width();
		var height = frame.height();
		$('.sle.active .source textarea').width(width-4).height(height-24);
	}
	
	Saint.sleExecute = function(cmd,parm) {

		if (cmd.indexOf('<')==0){
			parm=cmd;
			cmd="FormatBlock";
		}
		//if (cmd == "insertimage") parm = prompt("Filename or path to image","");
		if (cmd == "createlink") {
			parm = prompt("URL address of link (leave blank to remove link)","http://");
			if (parm=="" || parm=="http://"){cmd="Unlink"}
		}
		
		try {document.execCommand(cmd,false,parm);} catch(e){
			Saint.addError("SLE: Error executing formatting command: "+e);
		};
	};

	Saint.sleToggleSource = function () {
		$('.sle.active div.source .label-value').val($('.sle.active .wysiwyg .label-value').html());
		$('.sle.active div.source').show();
		Saint.sleResizeSourceEditor();
		$('.sle.active .wysiwyg').hide();
		$('.sle.active .wysiwyg .label-value').attr("contenteditable","false");
		Saint.sleRepopulateRevisions();
		Saint.sleWysiwygActive = false;
	};
	
	Saint.sleToggleWysiwyg = function () {
		$('.sle.active .wysiwyg .label-value').attr("contenteditable","true").html($('.sle.active div.source .label-value').val())
		$('.sle.active .wysiwyg').show();
		$('.sle.active div.source').hide();
		Saint.sleRepopulateRevisions();
		Saint.sleWysiwygActive = true;
	};
	
	Saint.sleStart = function(label) {
		// Contract admin overlay
		Saint.contractOverlay();
		
		// Flag editor as running.
		$('body').addClass('sle-active');
		label.addClass('sle-editing');
		
		// Create a label editor and fill it with data.
		var labelForm = $('.saint-templates > .sle.template.hidden').clone().removeClass('template').removeClass('hidden').addClass('active');
		labelForm.find('.cache').html(label.html());
		labelForm.find('input[name=label-name]').val(Saint.bubbleGet(label,'.saint-label',/^sln-(.*)$/));
		labelForm.find('div.label-value').html(label.html());
		labelForm.find('textarea[name=label-value]').val(label.html());
		
		// Add our new label editor to the dom.
		$('body').prepend(labelForm);
		
		// Calculate size and position for editor based on label.
		var margin = 10;
		var paddingX = 16;
		var paddingY = 54;
		var minX = 200;
		var minY = 50;

		// WYSIWYG labels need more room for the interface
		if (label.hasClass("wysiwyg")) {
			paddingY = 200;
			minX = 700;
			minY = 500;
		}
		
		var offset = label.offset();
		var initX = offset.left + (label.width()/2);
		var initY = offset.top-$(window).scrollTop();
		var newX = minX;
		var newY = minY;
		if (label.width()+paddingX > minX) {
			newX = label.width()+paddingX;
		}
		if (newX > labelForm.width()) {
			labelForm.width(newX-1);
			if (labelForm.width() > $(window).width()-(margin*2)) {
				labelForm.width($(window).width()-(margin*2));
			}
		}
		if (label.height()+paddingY > minY) {
			newY = label.height()+paddingY;
		}
		if (newY > labelForm.height()) {
			labelForm.height(newY);
			if (labelForm.height() > $(window).height()-(margin*2)) {
				labelForm.height($(window).height()-(margin*2));
			}
		}
		initX = initX - (labelForm.width()/2);
		if (initY-(paddingY/2) < 10) {
			labelForm.css("top","10px");
		} else if ( (initY-(paddingX/2) + labelForm.height() + margin) > $(window).height()) {
			labelForm.css("top",($(window).height()-labelForm.height()-margin)+"px");
		} else {
			labelForm.css("top",(initY-(paddingY/2))+"px");
		}
		if (initX < margin) {
			labelForm.css("left","10px");
		} else if ( (initX + labelForm.width() + margin) > $(window).width()) {
			labelForm.css("left",($(window).width()-labelForm.width()-margin)+"px");
		} else {
			labelForm.css("left",(initX-2)+"px");
		}
		// Load the number of saved revisions for this label from the server.
		Saint.sleGetNumRevs();
		
		// If it is a WYSIWYG block...
		if (label.hasClass("wysiwyg")) {
			// Set up the environment
			$('.sle.active').addClass("wysiwyg");
			Saint.sleToggleSource();
			$('.sle.active .link.switch.visual').hide()
			// Then start TinyMCE
			Saint.sleStartMCE($('.sle.active div.source textarea'),labelForm.width(),labelForm.height());
		} else {
			// Otherwise, just enable the regular Saint editor and focus on it.
			labelForm.find('div.label-value').attr("contenteditable","true").focus();
			// Tell the browser to use traditional tags (b, i, u) instead of styles.
			Saint.sleExecute('styleWithCSS',false);
		}
	};
	
	Saint.sleStop = function() {
		$('body').removeClass('sle-active');
		$('.sle-editing').html($('.sle.active .cache').html()).removeClass('sle-editing');
		$('.sle.active').remove();
	};
	
	Saint.sleGetNumRevs = function() {
		Saint.callHome("/system/?getlabelnumrevs="+$('.sle.active form input[name=label-name]').val(),null,Saint.sleGotNumRevs);
	};
	
	Saint.sleGotNumRevs = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				Saint.sleNumRevisions = realdata['revisions'];
				Saint.sleRepopulateRevisions();
			} else {
				$('.saint-ajax-indicator').addClass("error");
			}
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("Error requesting number of saved revisions. Please check the server error log for further information.");
		}
	};
	
	Saint.sleRepopulateRevisions = function() {
		if (Saint.sleNumRevisions > 1) {
			$('.sle.active .toolbar select[name=revision]').show();
			$('.sle.active .toolbar select[name=revision] option').remove();
			for (i = 0; i < Saint.sleNumRevisions; i++) {
				if (i == 0) {
					label = "Current";
				} else {
					label = "Revision "+(Saint.sleNumRevisions-i);
				}
				if (i == Saint.sleActiveRevision) {
					$('.sle.active .toolbar select[name=revision]').append($("<option>"+label+"</option>").attr("value", i).attr("selected","selected"));
				} else {
					$('.sle.active .toolbar select[name=revision]').append($("<option>"+label+"</option>").attr("value", i));
				}
			}
		} else {
			$('.sle.active .toolbar select[name=revision]').hide();
		}
	};
	
	Saint.sleGetRevision = function(revision) {
		Saint.callHome("/system/?getlabel="+$('.sle.active form input[name=label-name]').val()+"&revision="+revision,null,Saint.sleGotRevision);
		$('.sle.active').addClass("loading");
	};
	
	Saint.sleGotRevision = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				$('.sle.active div.label-value').html(realdata['label']);
				$('.sle.active textarea.label-value').val(realdata['label']);
				Saint.sleActiveRevision = realdata['revision'];
			} else {
				$('.saint-ajax-indicator').addClass("error");
			}
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("Error loading label. Please check the server error log for further information.");
		}
		$('.sle.active').removeClass("loading");
	};
	
	Saint.sleSave = function() {
		var stripped;
		var allowed_tags = '<a><i><b><u><p><ul><ol><li><img><center><h1><h2><h3><h4><h5><h6>';
		if ($('.sle.active').hasClass("wysiwyg")) {
			stripped = $('.sle.active textarea[name=label-value]').val();
		} else {
			if (Saint.sleWysiwygActive) {
				stripped = Saint.stripTags($('.sle.active div.label-value').html(),allowed_tags);
			} else {
				stripped = Saint.stripTags($('.sle.active textarea[name=label-value]').val(),allowed_tags);
			}
		}
		$('.sle.active textarea[name=label-value]').val(stripped);
		$('.sle.active .cache').html(stripped);
		var sdata = $('.sle.active form').serialize();
		Saint.callHome("/system",sdata,Saint.sleSaved);
	};
	
	Saint.sleSaved = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				$('.sle.active .toolbar select[name=revision] option.null').attr('selected','selected');
				Saint.sleActiveRevision = 0;
				Saint.sleGetNumRevs();
			} else {
				$('.saint-ajax-indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("Error saving label. Please check the server error log for further information.");
		}
	};
	
	Saint.sleStartMCE = function(mcetarget) {
		$(mcetarget).tinymce({
			// Location of TinyMCE script
			script_url : '/core/scripts/tinymce/tiny_mce.js',

			// General options
			theme : "advanced",
			plugins : "autolink,lists,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			// Example content CSS (should be your site CSS)
			content_css : "/core/styles/saint.css",

			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
			oninit: Saint.sleResizeMCE
			/*
			// Replace values for the template plugin
			template_replace_values : {
				username : "Some User",
				staffid : "991234"
			}*/
		});
	};
	
	Saint.sleResizeMCE = function() {
		$('.sle.active iframe').height($('.sle.active').height()-150);
		$('.sle.active iframe').width($('.sle.active').width()-2);
	}
	
	/* END Label Editor */
	
	/* START User Administration */
	
	$(document).on({
		'click': function(event) {
			window.location.replace(SAINT_URL + "/?action=logout");
		}
	},'.link.logout');
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions(".saint-admin-options.user-options");
		}
	},'.saint-admin-menu .link.users');
	
	$(document).on({
		'click': function(event) {
			Saint.editUser(event.target.id.replace('user-',''));
		}
	},'.saint-admin-options .user-list li');
	
	$(document).on({
		'click': function(event) {
			Saint.editUser(0);
		}
	},'.saint-admin-options.user-options .link.add');
	
	$(document).on({
		'click': function(event) {
			Saint.saveUser();
		}
	},'.saint-admin-options .user-edit-form .link.submit');
	
	$(document).on({
		'keypress': function(event) {
			if (event.which == "13") {
				event.stopPropagation();
				Saint.saveUser();
				return false;
			}
		}
	},'.user-edit-form form input');

	$(document).on({
		'click': function(event) {
			Saint.showOptions(".saint-admin-options.user-options");
		}
	},'.saint-admin-options .user-edit-form .link.cancel');
	
	Saint.editUser = function(uid) {
		$('.saint-admin-options.dynamic').html('');
		$('.saint-admin-options.dynamic').addClass("loading");
		Saint.showOptions(".saint-admin-options.dynamic");
		var url = '/user/?view=edit&id='+uid;
		Saint.callHome(url,null, Saint.loadDynamicOptionsBox);
	};
	
	Saint.saveUser = function() {
		if ($('.saint-admin-options .user-edit-form > form').valid()) {
			var postdata = $('.saint-admin-options .user-edit-form > form').serialize();
			Saint.callHome('/',postdata,Saint.savedUser);
		} else {
			$('.saint-admin-options.dynamic .error_display.submit').html("Please correct the errors in red to continue.").show();
			setTimeout("$('.saint-admin-options.dynamic .error_display').hide()",5000);
		}
	};
	
	Saint.savedUser = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				Saint.showOptions(".saint-admin-options.user-options");
				$('.saint-admin-options .user-list li').remove();
				
				for (i in realdata['users']) {
					$('.saint-admin-options .user-list').append($('<li id="'+realdata['users'][i][0]+'" class="link">'+realdata['users'][i][1]+'</li>'));
				}
			} else {
				alert(realdata['error']);
				$('.saint-ajax-indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem editing selected user. Please check the server error log for further information.",0);
		}
	};
	
	/* END User Administration */
	
	/* START Setting Management */
	
	$(document).on({
		'click': function(event) {
			Saint.saveSettings();
		}
	},'.saint-admin-options.site-options .settings .submit.link');
	
	Saint.saveSettings = function() {
		var postdata = $('.saint-admin-options.site-options form.settings').serialize();
		var url = '/system/';
		Saint.callHome(url, postdata, Saint.savedSettings);
	};
	
	Saint.savedSettings = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				Saint.addEvent("Saved settings.");
			} else {
				Saint.addError("There was a problem saving the selected category.");
				$('.saint-ajax-indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem saving the selected category. Please check the server error log for further information.",0);
		}
	};
	
	/* END Setting Management */
	
	/* START Category Management */
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions(".saint-admin-options.site-options");
		}
	},'#.saint-admin-menu .link.settings');
	
	$(document).on({
		'click': function(event) {
			Saint.saveCategory();
		}
	},'#saint-add-category-submit');
	
	$(document).on({
		'keypress': function(event) {
			if (event.which == "13") {
				event.stopPropagation();
				Saint.saveCategory();
				return false;
			}
		}
	},'#saint-add-category');

	$(document).on({
		'click': function(event) {
			var editid = Saint.bubbleGet(event.target,'.category-edit',/^cat-(.*)$/);
			if ($(event.target).hasClass("delete")) {
				$('#saint-set-category-id').val(editid);
				$('#saint-add-category').val("");
				$('#saint-delete-category').val(1);
				var postdata = $('.saint-admin-options.site-options form.categories').serialize();
				var url = '/system/';
				Saint.callHome(url, postdata, Saint.savedCategory);
			} else {
				var editname = $(event.target).text().replace(/\s+$/mg,"");
				$('#saint-set-category-id').val(editid);
				$('#saint-add-category').val(editname);
				$('#saint-add-category-submit').html("Save");
				$('#saint-add-category-cancel').removeClass("hidden");
			}
		}
	},'.category-edit');
	
	$(document).on({
		'click': function(event) {
			$('#saint-set-category-id').val(0);
			$('#saint-add-category').val('');
			$('#saint-add-category-submit').html("Add");
			$('#saint-add-category-cancel').addClass("hidden");
		}
	},'#saint-add-category-cancel');
	
	
	Saint.saveCategory = function() {
		var postdata = $('.saint-admin-options.site-options form.categories').serialize();
		var url = '/system/';
		Saint.callHome(url, postdata, Saint.savedCategory);
	};
	
	Saint.savedCategory = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				$("#saint-delete-category").val('0');
				$("#saint-set-category-id").val('0');
				$("#saint-add-category").val('');
				$('.saint-admin-options .category-list li').remove();
				for (i in realdata['categories']) {
					$('.saint-admin-options .category-list').append($('<li class="link category-edit cat-'+realdata['categories'][i][0]+'">'
						+realdata['categories'][i][1]+'<span class="delete close-button">&nbsp;</span></li>'));
				}
			} else {
				Saint.addError("There was a problem saving the selected category.");
				$('.saint-ajax-indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem saving the selected category. Please check the server error log for further information.",0);
		}
	};
	
	/* END Category Management */
	
	/* START Page Management */
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions(".saint-admin-options.page-options");
		}
	},'.saint-admin-menu .link.pages');
	
	/**
	 * Start editing the dynamic items on the current page.
	 */
	$(document).on({
		'click': function(event) {
			if (Saint.editing) {
				Saint.stopPageEdit();
			} else {
				Saint.startPageEdit();
			}
		}
	},'.saint-admin-options.page-options .link.edit, .saint-admin-overlay .saint-logo');

	$(document).on({
		'click': function(event) {
			Saint.stopPageEdit();
			Saint.showOptions(".saint-admin-options.page-add");
		}
	},'.saint-admin-options.page-options .link.add');

	$(document).on({
		'click': function(event) {
			if (confirm("Are you sure you wish to delete the current page?")) {
				Saint.deleteCurrentPage();
			}
		}
	},'.saint-admin-options.page-options .link.delete');
	
	$(document).on({
		'click': function(event) {
			$('.saint-admin-options.current-page').removeClass("visible");
		}
	},'.saint-admin-options.current-page .close-button');

	$(document).on({
		'click': function(event) {
			Saint.savePageOptions();
			Saint.stopPageEdit();
		}
	},'.saint-admin-options.current-page .link.save');
	
	$(document).on({
		'click': function(event) {
			Saint.addPage();
		}
	},'.saint-admin-options.page-add .link.add');
	
	$(document).on({
		'keyup': function(event) {
			if (event.which == "13") {
				event.stopPropagation();
				Saint.addPage();
				return false;
			}
		}
	},'.saint-admin-options.page-add form input');
	
	/**
	 * Disable all links when the page is in edit mode.
	 */
	$(document).on({
		'click': function(event) {
			if ($('body').hasClass("editing")) {
				event.stopPropagation();
				return false;
			} else
				return true;
		}
	},'a, a *');
	
	Saint.addPage = function() {
		postdata = $('.saint-admin-options.page-add form').serialize();
		Saint.callHome('/system/',postdata,Saint.addedPage);
	};
	
	Saint.addedPage = function(data) {
		var success;
		try {
			realdata = JSON.parse(data);
			success = realdata['success'];
			Saint.setActionLog(realdata['actionlog']);
		} catch (e) {
			success = false;
		}
		if (success) {
			Saint.showOptions(".saint-admin-options.page-options");
			$(':input','.saint-admin-options.page-add')
			 .not(':button, :submit, :reset, :hidden')
			 .val('')
			 .removeAttr('checked')
			 .removeAttr('selected');
			$('.saint-admin-options .page-list li').remove();
			
			for (i in realdata['pages']) {
				$('.saint-admin-options .page-list').append($('<li><a class="sublist" href="'+SAINT_URL+'/'+realdata['pages'][i][0]+'">'+realdata['pages'][i][1]+'</a></li>'));
			}
		} else {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem adding your page. Please check the error log for further information.",0);
		}
	};
	
	Saint.savePageOptions = function() {
		postdata = $('.saint-admin-options.current-page form').serialize();
		Saint.callHome('/system',postdata,Saint.savedPageOptions);
	};
	
	Saint.savedPageOptions = function(data) {
		var success;
		try {
			realdata = JSON.parse(data);
			success = realdata['success'];
			Saint.setActionLog(realdata['actionlog']);
		} catch (e) {
			success = false;
		}
		if (success) {
			Saint.showOptions(".saint-admin-options.page-options");
			$(':input','.saint-admin-options.page-add')
			 .not(':button, :submit, :reset, :hidden')
			 .val('')
			 .removeAttr('checked')
			 .removeAttr('selected');
		} else {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem saving your page options. Please check the error log for further information.",0);
		}
	};
	
	Saint.startPageEdit = function() {
		$(document.body).addClass("editing");
		Saint.editing = true;
		$('.saint-admin-options .link.edit').html("Stop Editing");
		$('.saint-admin-options.current-page').addClass("visible");
		//$('.editable').addClass("editnow");
		//$('.repeating').addClass("editnow");
		Saint.contractOverlay();
	};
	
	Saint.stopPageEdit = function() {
		$(document.body).removeClass("editing");
		Saint.sleStop();
		Saint.editing = false;
		if ($('.saint-admin-block.add-block').hasClass("active")) {
			Saint.saveAddBox();
			Saint.closeAddBox();
		}
		$('.saint-admin-options .link.edit').html("Edit This Page");
		$('.saint-admin-options.current-page').removeClass("visible");
		$('.editable').removeClass("editnow");
		$('.repeating').removeClass("editnow");
	};

	Saint.deleteCurrentPage = function() {
		var cpid = $('.saint-admin-options.current-page').find('input[name=saint-edit-page-id]').val();
		Saint.callHome("/system/?delpage="+cpid,null,Saint.deletedCurrentPage);
	};
	
	Saint.deletedCurrentPage = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				window.location.replace(SAINT_URL+'/');
			} else {
				$('.saint-ajax-indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem deleting your page. Please check the server error log for further information.",0);
		}
	};
	
	/* END Page Management */
	
	/* START Block Management */
	
	Saint.sbeUriOverride = false;
	
	$(document).on({
		'click': function(event) {
			Saint.saveAddBox();
			Saint.closeAddBox();
		}
	},'#saint-add-block-save');
	
	$(document).on({
		'click': function(event) {
			if (confirm("Are you sure you wish to delete this block? Any changes made will be lost.")) {
				$('#saint-block-setting-enabled').val('0');
				Saint.saveAddBox();
				Saint.closeAddBox();
			}
		}
	},'#saint-add-block-delete');
	
	$(document).on({
		'click': function(event) {
			Saint.closeAddBox();
		}
	},'#saint-add-block-cancel');
	
	$(document).on({
		'click': function(event) {
			Saint.addBlockId = 0;
			postdata = 'block='+Saint.sbeGetName(event.target);
			url = '/system/';
			Saint.callHome(url, postdata, Saint.loadAddBox);
			Saint.openAddBox();
		}
	},'.repeating > .add-button');

	$(document).on({
		'click': function(event) {
			Saint.addBlockId = Saint.sbeGetId(event.target);
			postdata = 'block='+Saint.sbeGetName(event.target)+'&blockid='+Saint.addBlockId;
			url = '/system/';
			Saint.callHome(url, postdata, Saint.loadAddBox);
			Saint.openAddBox();
		}
	},'.block-item > .edit-button');

	/**
	 * Automatically update settings in the preview box.
	 */
	$(document).on({
		'keyup': function(event) {
			var setting = event.currentTarget.id.replace(/^saint-block-setting-/,'');
			// For automatic URI generation
			if (setting == "uri") {
				if ($('#saint-block-setting-uri').val() == "") {
					Saint.sbeUriOverride = false;
				} else {
					Saint.sbeUriOverride = true;
				}
			}
			if ($(event.currentTarget).hasClass("uri-indicator") && !Saint.sbeUriOverride) {
				$('#saint-block-setting-uri').val($(event.currentTarget).val().replace(/[\s*]/g,'-').replace(/[^\w-]/g,'').toLowerCase());
			}
			$('#saint-add-block-data .sbs-'+setting).html($(event.currentTarget).val());
		}
	},'#saint-add-block-settings input');
	
	Saint.openAddBox = function() {
		$('.saint-admin-block.add-block').addClass("loading").addClass("active");
	};
	
	Saint.closeAddBox = function() {
		$('.saint-admin-block.add-block').removeClass("active");
	};
	
	Saint.loadAddBox = function(data) {
		$('.saint-admin-block.add-block .load').html(data);
		$('.editable').addClass("editnow");
		$('#saint-add-block-data .editable').addClass('editnow');
		$('.saint-admin-block.add-block').removeClass("loading");
		// Special code for blog
		if ($('#saint-block-setting-uri').val() == "") {
			Saint.sbeUriOverride = false;
		} else {
			Saint.sbeUriOverride = true;
		}
	};
	
	Saint.saveAddBox = function() {
		$('.saint-admin-block.add-block').addClass("loading");
		postdata = $('#saint-add-block-settings').serialize();
		if (window.location.search.match(/\?/)) {
			indicator = '&';
		} else {
			indicator = '?'; }
		Saint.callHome(SAINT_BASE_URL+window.location.pathname+"/"+window.location.search+indicator+"edit="+$('#saint-block-setting-id').val(),postdata,Saint.savedAddBox);
	};
	
	Saint.savedAddBox = function(data) {
		$('.saint-admin-block.add-block').removeClass("loading");
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				if (realdata['data']) {
					$(".sbn-"+realdata['block']).html(realdata['data']);
				} else {
					Saint.refreshPage();
				}
			} else {
				$('.saint-ajax-indicator').addClass("error");
				Saint.setActionLog(realdata.actionlog);
			}
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem adding your block. Please check the server error log for further information.",0);
		}	
	};
	
	Saint.sbeGetName = function(elem) {
		return Saint.bubbleGet(elem,".saint-block, .repeating",/^sbn-(.*)$/);
	};
	
	Saint.sbeGetId = function(elem) {
		return Saint.bubbleGet(elem,".edit-button",/^sbid-(.*)$/);
	};

	/* END Block Management */
	
	/* START Admin Overlay */
	
	/**
	 * Expand admin overlay when mouse enters,
	 * contract when mouse leaves.
	 */
	$(document).on({
		'mouseenter': function(event) {
			Saint.expandOverlay();
		},
		'mouseleave': function(event) {
			Saint.contractOverlay();
		}
	},'.saint-admin-overlay');
	
	/**
	 * Stops the admin overlay from being contracted while an input field has focus.
	 */
	$(document).on({
		'focusin': function(event) {
			$('.saint-admin-overlay').removeClass("expanded");
		},
		'focusout': function(event) {
			$('.saint-admin-overlay').addClass("expanded");
		}
	},'.saint-admin-overlay form');

	$(document).on({
		'click': function(event) {
			$(event.target.parentNode).removeClass("saint-list-contracted");
			$(event.target.parentNode).addClass("saint-list-expanded");
		}
	},'.saint-list-contracted > .trigger');

	$(document).on({
		'click': function(event) {
			$(event.target.parentNode).removeClass("saint-list-expanded");
			$(event.target.parentNode).addClass("saint-list-contracted");
		}
	},'.saint-list-expanded > .trigger');

	Saint.expandOverlay = function() {
		if ($('.saint-admin-overlay').hasClass("contracted")) {
		$('.saint-admin-overlay').animate({
		    width: '500px',
		    height: '300px'
		  }, 600, function() {
			$('.saint-admin-overlay').removeClass("contracted");
			$('.saint-admin-overlay').addClass("expanded");
		  });
		}
	};
	
	Saint.contractOverlay = function() {
		if ($('.saint-admin-overlay').hasClass("expanded")) {
		  $('.saint-admin-overlay').animate({
		    width: '100px',
		    height: '20px'
		  }, 600, function() {
			$('.saint-admin-overlay').removeClass("expanded");
			$('.saint-admin-overlay').addClass("contracted");
		  });
		}
	};
	
	Saint.showOptions = function(thediv) {
		$('.saint-admin-options').addClass("hidden");
		$(thediv).removeClass("hidden");
	};
	
	Saint.addError = function(error, severity) {
		var esize = Saint.errors.push(error);
		if (!$('.saint-ajax-indicator').hasClass("error"))
			$('.saint-ajax-indicator').addClass("error");
		if (severity == 0)
			alert(error);
		$('.saint-action-log').prepend(error);
		return esize-1;
	};
	
	Saint.addEvent = function(event) {
		$('.saint-action-log').append("<p>"+ event +"</p>");
	};
	
	Saint.clearActionLog = function() {
		$('.saint-action-log').html('');
	}
	
	Saint.setActionLog = function(datalog) {
		Saint.clearActionLog();
		for (action in datalog) {
			Saint.addEvent(datalog[action]);
		}
	}
	
	Saint.fixError = function(errorid) {
		Saint.errors.splice(tcid,1);
		if(!Saint.errors.length)
			$('.saint-ajax-indicator').removeClass("error");
	};
	
	Saint.clearForm = function(formselector, clearhidden) {
		var toskip = ':button, :submit, :reset';
		if (clearhidden == true) {
			toskip += ', :hidden';
		}
		$(':input',formselector)
		 .not(toskip)
		 .val('')
		 .removeAttr('checked')
		 .removeAttr('selected');
	};
	
	Saint.loadDynamicOptionsBox = function(data) {
		$('.saint-admin-options.dynamic').html(data);
		$('.saint-admin-options.dynamic').removeClass("loading");
		$('.saint-admin-options.dynamic form').validate();
		$('.saint-admin-options.dynamic .error_display').hide();
	};
	
	/* END Admin Overlay */
	
	/* START File Manager */
	
	$(document).on({
		'click': function(event) {
			if (Saint.fileManagerIsOpen) {
				Saint.stopEditFile();
				Saint.closeFileManager();
			} else {
				Saint.openFileManager();
			}
		}
	},'.saint-admin-menu .link.files');

	$(document).on({
		'click': function(event) {
			Saint.stopEditFile();
			Saint.closeFileManager();
		}
	},'#sfm-close-button');

	$(document).on({
		'click': function(event) {
			if (Saint.fileManagerIsOpen) {
				Saint.stopEditFile();
				Saint.closeFileManager();
			} else {
				var filelabel = Saint.bubbleGet(event.target,'.saint-image',/^sfl-(.*)$/);
				var labelid = Saint.bubbleGet(event.target,'.sfl-image',/^sfid-(.*)$/);
				Saint.sflWidth = Saint.bubbleGet(event.target,'.saint-image',/^width-(.*)$/);
				Saint.sflHeight = Saint.bubbleGet(event.target,'.saint-image',/^height-(.*)$/);
				
				Saint.openFileManager(filelabel,labelid);
			}
		}
	},'.editing .saint-image.editable img');

	$(document).on({
		'click': function(event) {
			Saint.startEditFile(event.currentTarget.parentNode);
		}
	},'#saint-file-manager-data img.link');
	
	$(document).on({
		'click': function(event) {
			if ($('#saint-uploader').hasClass("active")) {
				$('#saint-uploader').removeClass("active");
			} else {
				$('#saint-uploader').addClass("active");
			}
		}
	},'#saint-uploader h3');

	$(document).on({
		'click': function(event) {
			var selpage = event.currentTarget.id.replace(/sfm-page-/,'');
			Saint.sfmSelectPage(selpage);
		}
	},'.saint-admin-block.file-manager .sfm-pager span.link');
	
	$(document).on({
		'click': function(event) {
			Saint.sfmSubmit();
		}
	},'#saint-file-info .form-submit');

	$(document).on({
		'click': function(event) {
			Saint.sfmReset();
		}
	},'#saint-file-info .form-cancel');
	
	$(document).on({
		'keyup': function(event) {
			if (event.which == 13) {
				Saint.sfmSubmit();
			}
		}
	},'#saint-file-info form input');
	
	$(document).on({
		'keyup': function(event) {
			if (event.which == 27) {
				Saint.sfmReset();
			}
		}
	},'#saint-file-info form');

	Saint.sfmReset = function() {
		Saint.clearForm('#saint-file-info form');
		$('#saint-file-mode').val("search");
		Saint.sfmSelectPage(0);
		$('#saint-file-info .form-submit').html('Search');
		$('#saint-file-info .form-cancel').html('Reset');
	};
	
	Saint.sfmSubmit = function() {
		var postdata = $('#saint-file-info form').serialize();
		postdata += "&saint-file-label-height="+Saint.sflHeight+"&saint-file-label-width="+Saint.sflWidth;
		Saint.sfmSelectPage(0,postdata);
		if (Saint.sfmFileToEdit) {
			Saint.sfmFileToEdit = 0;
			Saint.closeFileManager();
		}
	};
	
	Saint.sfmCenterImage = function(target) {
		var curFile = $(target).find(".sfm-editblock");
		var parentHeight = curFile.innerHeight();
		var imgHeight = $(target).find("img").outerHeight();
		var margin = (parentHeight/2)-(imgHeight/2);
		$(target).find("img").css("margin-top",margin+"px");
	};
	
	Saint.startEditFile = function(target) {
		Saint.sfmCurrentlyEditing = target;
		var curFile = $(target).find(".sfm-editblock");
		curFile.removeClass("hidden");
		
		Saint.sfmCenterImage(target);
		
		// Copy the file data to the form
		$("#saint-file-mode").val("edit");
		$("#saint-file-id").val(curFile.parent().find(".id").html());
		$("#saint-file-label").val(Saint.sfmLabelToEdit);
		$("#saint-file-title").val(curFile.parent().find(".title").html());
		$("#saint-file-keywords").val(curFile.parent().find(".keywords").html());
		$("#saint-file-description").val(curFile.parent().find(".description").html());
		$("#saint-file-categories").val(curFile.parent().find(".categories").html().split(","));
		
		if (Saint.sfmFileToEdit) {
			$('#saint-file-info .form-submit').html('Use This File');
		} else {
			$('#saint-file-info .form-submit').html('Save');
		}
		if (Saint.sfmFileToEdit) {
			$('#saint-file-info .form-cancel').html('Choose New File');
		} else {
			$('#saint-file-info .form-cancel').html('Cancel');
		}
	};
	
	Saint.stopEditFile = function() {
		Saint.sfmCurrentlyEditing = 0;
		$(".sfm-editblock").addClass("hidden");
		Saint.clearForm('#saint-file-info form');
		$('#saint-file-mode').val("search");
	};

	Saint.openFileManager = function(label,file) {
		if (label != null) {
			Saint.sfmFileToEdit = file;
			Saint.sfmLabelToEdit = label; }
		Saint.fileManagerIsOpen = true;
		var callurl = "/filemanager";
		if (file != null) {
			Saint.sfmImageToLoad = file;
			callurl += "/?fid="+file;
		}
		Saint.callHome(callurl,'',Saint.loadedFileManager);
		Saint.sfmAnimationIsComplete = false;
		$('.saint-admin-block.file-manager').addClass("loading").addClass("active");
		
		setTimeout(function(){
			Saint.sfmAnimationIsComplete = true;
			Saint.openImage();
		},600);
	};
	
	Saint.closeFileManager = function() {
		Saint.sflHeight = 0;
		Saint.sflWidth = 0;
		Saint.fileManagerIsOpen = false;
		$('#saint-file-manager-data .saint-loadable-content').html("&nbsp;");
		$('.saint-admin-block.file-manager').removeClass("active");
	};
	
	Saint.loadedFileManager = function(data) {
		$('.saint-admin-block.file-manager .load').html(data);
		if (Saint.sfmAnimationIsComplete) {
			Saint.openImage(); }
	};
	
	Saint.openImage = function() {
		if (Saint.sfmImageToLoad) {
			Saint.startEditFile($('#sfm-'+Saint.sfmImageToLoad).parent());
			Saint.sfmImageToLoad = 0;
			$('.saint-admin-block.file-manager').removeClass("loading");
		} else if (!Saint.sfmImageToLoad) {
			$('.saint-admin-block.file-manager').removeClass("loading"); }
	};
	
	Saint.sfmSelectPage = function(pagenum,postdata) {
		Saint.sfmcurpage = pagenum;
		$('#saint-file-manager-data .saint-admin-block .overlay').addClass("loading");
		Saint.callHome("/filemanager/?view=file-list&sfmcurpage="+pagenum, postdata, Saint.sfmLoadPage);
	};
	
	Saint.sfmLoadPage = function(data) {
		$('#saint-file-manager-data .saint-loadable-content').html("&nbsp;");
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				if (realdata['url'] && realdata['sfl'] && realdata['sfid']) {
					$(".sfl-"+realdata['sfl']+" img")[0].className = $(".sfl-"+realdata['sfl']+" img")[0].className.replace(/sfid-\d{1,10}/g,'');
					$(".sfl-"+realdata['sfl']+" img").attr("src",realdata['url']).addClass("sfid-"+realdata['sfid']);
				}
			} else {
				$('.saint-ajax-indicator').addClass("error");
				Saint.setActionLog(realdata.actionlog);
			}
		} catch (e) {
			$('#saint-file-manager-data .saint-loadable-content').html(data);
			if ($('#sfm-status').html() != "") {
				$('#sfm-message').addClass('enabled');
				setTimeout("$('#sfm-message').removeClass('enabled')",5000);
			}
			if ($('#sfm-status').html() == "saved") {
				Saint.clearForm('#saint-file-info form');
				$('#saint-file-mode').val("search");
				$('#saint-file-info .form-submit').html('Search');
				$('#saint-file-info .form-cancel').html('Reset');
			}
			$('.saint-admin-block.add-block').removeClass("loading");
			$('#saint-file-manager-data .saint-admin-block .overlay').removeClass("loading");
		}
	};

	/* END File Manager */
	
	/* START Shop Manager */

	$(document).on({
		'click': function(event) {
			$('.saint-admin-block.shop-manager').addClass("loading").addClass("active");
			Saint.callHome('/shop/?view=transactions','',Saint.loadedShopManager);
		}
	},'#ssm-link-transactions');
	
	$(document).on({
		'click': function(event) {
			$('.saint-admin-block.shop-manager').addClass("loading").addClass("active");
			Saint.callHome('/shop/?view=discounts','',Saint.loadedShopManager);
		}
	},'#ssm-link-discounts');
	
	$(document).on({
		'click': function(event) {
			Saint.closeShopManager();
		}
	},'#ssm-close-button');
	
	$(document).on({
		'click': function(event) {
			if (Saint.shopManagerIsOpen) {
				Saint.closeShopManager();
			} else {
				if (Saint.editing) {
					Saint.stopPageEdit(); }
				Saint.openShopManager();
			}
		}
	},'.saint-admin-menu .link.shop');
	
	Saint.openShopManager = function() {
		$('.saint-admin-block.shop-manager').addClass("loading").addClass("active");
		Saint.callHome('/shop/?view=transactions','',Saint.loadedShopManager);
		Saint.shopManagerIsOpen = true;
	};
	
	Saint.closeShopManager = function() {
		$('.saint-admin-block.shop-manager').removeClass("active");
		Saint.shopManagerIsOpen = false;
	};
	
	Saint.loadedShopManager = function(data) {
		$('.saint-admin-block.shop-manager .load').html(data);
		$('.saint-admin-block.shop-manager').removeClass("loading");
	};
	
	/* END Shop Manager */
	
	/* START World Events */
	
	Saint.validationTimer = new Array();
	
	$(window).resize(function() {
		if (Saint.sfmCurrentlyEditing) {
			Saint.sfmCenterImage(Saint.sfmCurrentlyEditing);
		}
	});
	
	$(document).on({
		'keyup': function(event) {
			var setting = Saint.bubbleGet(event.currentTarget,'.saint-validate',/^saint-validate-(.*)$/)
			if (Saint.validationTimer[setting]) {
				clearTimeout(Saint.validationTimer[setting]);
				Saint.validationTimer[setting] = 0;
			}
			if ($(event.currentTarget).val() == "" || $(event.currentTarget).val() == $(event.currentTarget)[0].defaultValue) {
				clearTimeout(Saint.validationTimer[setting]);
				$('.hud.'+setting).hide();
			} else {
				Saint.validationTimer[setting] = setTimeout(function() { Saint.validateField(setting) },1000);
			}
		}
	},'.saint-validate');
	
	Saint.refreshPage = function() {
		location.reload(true);
	};

	Saint.stripTags = function (input, allowed) {
	    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
	    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	    });
	}
	
	Saint.callHome = function(url, postdata, complete, timeout, retries, tcid, errorno) {
		if (url.match(/^http/) == null) {
			url = SAINT_URL + url;
		}
		if (timeout == null)
			timeout = 10000;
		if (retries == null)
			retries = 3;
		if (tcid == null)
			tcid = Saint.pushConnection(url, postdata);
		if (retries <= 0) {
			$('.saint-ajax-indicator').addClass("error");
			if (errorno == null)
				errorno = Saint.addError("There has been a problem communicating with the server. Please check your network connections before continuing. Any changes you make at this time will not be saved.",0);
			return 0;
		}
		$.ajax({
			type : 'POST',
			url : url,
			timeout: timeout,
			dataType : 'html',
			data: postdata,
			success : function(data) {
				Saint.popConnection(tcid);
				if ($.isFunction(complete)) {
					complete(data); }
				return 1;
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				return Saint.callHome(url, postdata, complete, timeout*2, retries-1,tcid,errorno);
			}
		});
	};
	
	Saint.pushConnection = function(url, data) {
		if (!$('.saint-ajax-indicator').hasClass("enabled"))
			$('.saint-ajax-indicator').addClass("enabled");
		ncid = Saint.connections.push(new Array(url,data));
		return ncid-1;
	};
	
	Saint.popConnection = function(tcid) {
		Saint.connections.splice(tcid,1);
		if(!Saint.connections.length)
			$('.saint-ajax-indicator').removeClass("enabled");
	};
	
	Saint.bubbleGet = function(elem,clmatch,pattern) {
		var cur = $(elem);
		var go = true;
		var id = '';
		while (go) {
			if (cur.is(clmatch)) {
				if (pattern == null) {
					id = cur;
				} else {
					var curClasses = cur.attr('class').split(/\s+/);
					$.each(curClasses, function(index, item){
					    if (matches = item.match(pattern)) {
					    	id = matches[1];
					    	go = false;
					    }
					});
				}
				go = false;
			}
			if (cur.prop("tagName") == "BODY") {
				go = false;
			} else {
				cur = cur.parent();
			}
		}
		return id;
	};
	
	Saint.validateField = function(field) {
		var field_selector = ".saint-validate.saint-validate-"+field;
		if ($(field_selector).val() == $(field_selector)[0].defaultValue) {
			$('.hud.username').hide();
		} else {
			Saint.callHome('/system/?check-'+field+'='+$(field_selector).val(),null,Saint.validatedField);
		}
	};
	
	Saint.validatedField = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				if (realdata['available']) {
					$('.hud.'+realdata['setting']).removeClass("error").html(realdata['message']).show();
				} else {
					$('.hud.'+realdata['setting']).addClass("error").html(realdata['message']).show();
				}
			} else {
				Saint.addError("There was a problem checking the availability of the given username. Check the error log for details.");
				$('.saint-ajax-indicator').addClass("error");
			}
		} catch (e) {
			$('.saint-ajax-indicator').addClass("error");
			Saint.addError("There was a problem checking the availability of the given username. Check the error log for details.");
		}
	};
	
	/* END World Events */
});
