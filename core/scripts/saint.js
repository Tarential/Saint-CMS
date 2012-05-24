$(document).ready(function() {
	$('#saint_admin_overlay').addClass("contracted");
	$('#saint_admin_page_options').removeClass("hidden");
	$('.focus').focus();
	
	if ($('#saint-paypal-buynow').hasClass("buynow")) {
		$('#saint-paypal-buynow').submit();
	};
	
	$.ajaxSetup({
		type: 'POST',
		timeout: 5000,
		cache: false
	});
	
	$(document).on({
		'click': function(event) {
			if (Saint.editing)
				return false;
			else
				return true;
		}
	},'a');
	
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
			Saint.loadWysiwyg(event.currentTarget.id);
		}
	},'.editing .saint-wysiwyg');
	
	/**
	 * Actions to take place on editable labels.
	 */
	$(document).on({
		'click': function(event) {
			if (Saint.editing) {
				if ($(this).hasClass('editing'))
					event.stopPropagation();
				else
					Saint.startEdit($(this));
			}
		},
		'focusout': function(event) {
			Saint.saveLabel($(this));
			Saint.stopEdit($(this));
		},
		'keyup': function(event) {
			if (event.which == 27) {
				Saint.stopEdit($(this));
				return false;
			}
		}
	},'span.editable');
	
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
	},'#saint_admin_overlay');
	
	/**
	 * Stops the admin overlay from being contracted while an input field has focus.
	 */
	$(document).on({
		'focusin': function(event) {
			$('#saint_admin_overlay').removeClass("expanded");
		},
		'focusout': function(event) {
			$('#saint_admin_overlay').addClass("expanded");
		}
	},'#saint_admin_overlay form');
	
	$(document).on({
		'click': function(event) {
			if (Saint.fileManagerIsOpen) {
				Saint.stopEditFile();
				Saint.closeFileManager();
			} else {
				Saint.openFileManager();
			}
		}
	},'#saint_menu_link_files');
	
	$(document).on({
		'click': function(event) {
			window.location.replace("/action.logout");
		}
	},'#saint_menu_link_logout');
	
	$(document).on({
		'click': function(event) {
			Saint.stopEditFile();
			Saint.closeFileManager();
		}
	},'#sfm-close-button');
	
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
	},'#saint_menu_link_shop');
	
	$(document).on({
		'click': function(event) {
			$('#saint-admin-shop-manager').addClass("loading").addClass("active");
			Saint.callHome('/shop/view.transactions','',Saint.loadedShopManager);
		}
	},'#ssm-link-transactions');
	
	$(document).on({
		'click': function(event) {
			$('#saint-admin-shop-manager').addClass("loading").addClass("active");
			Saint.callHome('/shop/view.discounts','',Saint.loadedShopManager);
		}
	},'#ssm-link-discounts');
	
	$(document).on({
		'click': function(event) {
			if (event.currentTarget.parentNode.tagName == "DIV")
				var filelabel = event.currentTarget.parentNode;
			else
				var filelabel = event.currentTarget.parentNode.parentNode;
			if (Saint.fileManagerIsOpen) {
				Saint.stopEditFile();
				Saint.closeFileManager();
			} else {
				Saint.openFileManager(filelabel);
			}
		}
	},'.saint-image.editable.editnow img');
	
	/**
	 * Start editing the dynamic items on the current page.
	 * This will also disable links so that editable text
	 * which is part of a link can be clicked safely.
	 */
	$(document).on({
		'click': function(event) {
			if (Saint.editing) {
				Saint.stopPageEdit();
			} else {
				Saint.startPageEdit();
			}
		}
	},'#saint_admin_po_edit');

	$(document).on({
		'click': function(event) {
			Saint.stopPageEdit();
			Saint.showOptions("#saint_admin_page_add");
		}
	},'#saint_admin_po_add');

	$(document).on({
		'click': function(event) {
			if (confirm("Are you sure you wish to delete the current page?")) {
				Saint.deleteCurrentPage();
			}
		}
	},'#saint_admin_po_delete');
	
	$(document).on({
		'click': function(event) {
			$('#saint-admin-page-options').removeClass("visible");
		}
	},'#saint-page-options-close');

	$(document).on({
		'click': function(event) {
			Saint.savePageOptions();
			Saint.stopPageEdit();
		}
	},'#saint-page-options-save');
	
	$(document).on({
		'click': function(event) {
			Saint.addPage();
		}
	},'#saint_admin_page_add_submit');
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions("#saint_admin_page_options");
		}
	},'.saint_menu_link_pages');
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions("#saint_admin_user_options");
		}
	},'#saint_menu_link_users');
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions("#saint_admin_category_options");
		}
	},'#saint_menu_link_categories');

	$(document).on({
		'click': function(event) {
			Saint.editUser(event.target.id.replace('user-',''));
		}
	},'#saint_admin_user_list li');
	
	$(document).on({
		'click': function(event) {
			Saint.editUser(0);
		}
	},'#saint_admin_uo_add');
	
	$(document).on({
		'click': function(event) {
			var postdata = $('#saint_user_edit > form').serialize();
			Saint.callHome('/',postdata,Saint.savedUser);
		}
	},'#saint_edit_user_submit');
	
	$(document).on({
		'click': function(event) {
			Saint.showOptions("#saint_admin_user_options");
		}
	},'#saint_edit_user_cancel');
	
	$(document).on({
		'click': function(event) {
			id = event.target.parentNode.id;
			if (id == "")
				id = event.target.parentNode.parentNode.id;
			id = id.replace("saint_","");
			postdata = 'block='+id;
			url = '/system/';
			Saint.addBlockId = id;
			Saint.callHome(url, postdata, Saint.loadAddBox);
			Saint.openAddBox();
		}
	},'.repeating > .add-button');

	$(document).on({
		'click': function(event) {
			bname = event.target.parentNode.parentNode.id;
			bname = bname.replace("saint_","");
			id = event.target.id;
			postdata = 'block='+bname+'&blockid='+id;
			url = '/system/';
			Saint.addBlockId = id;
			Saint.callHome(url, postdata, Saint.loadAddBox);
			Saint.openAddBox();
		}
	},'.block-item > .edit-button');
	
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
	
	$(document).on({
		'click': function(event) {
			var postdata = $('#saint_admin_category_options form').serialize();
			var url = '/system/';
			Saint.callHome(url, postdata, Saint.savedCategory);
		}
	},'#saint-add-category-submit');

	$(document).on({
		'click': function(event) {
			if ($(event.target).hasClass("delete")) {
				var editid = $(event.target.parentNode).attr('id').replace(/cat-/mg,"");
				var editname = $(event.target).text();
				$('#saint-set-category-id').val(editid);
				$('#saint-add-category').val(editname);
				$('#saint-delete-category').val(1);
				var postdata = $('#saint_admin_category_options form').serialize();
				var url = '/system/';
				Saint.callHome(url, postdata, Saint.savedCategory);
			} else {
				var editid = $(event.target).attr('id').replace(/cat-/mg,"");
				var editname = $(event.target).text();
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
	
	$(document).on({
		'click': function(event) {
			Saint.saveWysiwyg();
		}
	},'#saint-save-wysiwyg');
	
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
	},'#saint-admin-file-manager .sfm-pager span.link');
	
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
	
	$(document).on({
		'click': function(event) {
			$('#saint-paypal-buynow form').submit();
		}
	},'.saint-cart-title.link');
	
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
	
	$(window).resize(function() {
		if (Saint.sfmCurrentlyEditing) {
			Saint.sfmCenterImage(Saint.sfmCurrentlyEditing);
		}
	});
	
	Saint.sfmReset = function() {
		Saint.clearForm('#saint-file-info form');
		$('#saint-file-mode').val("search");
		Saint.sfmSelectPage(0);
		$('#saint-file-info .form-submit').html('Search');
		$('#saint-file-info .form-cancel').html('Reset');
	};
	
	Saint.sfmSubmit = function() {
		var postdata = $('#saint-file-info form').serialize();
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
	
	/*
	Saint.saveEditFile = function() {
		var postdata = $('#saint-file-info form').serialize();
		Saint.callHome('/system/',postdata,Saint.savedEditFile);
	};
	
	Saint.savedEditFile = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				Saint.stopEditFile();
				Saint.sfmSelectPage(Saint.sfmcurpage);
			} else {
				Saint.addError("There was a problem saving your changes. Please check the error log for further information.",0);
				$('#saint_ajax_indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem saving your changes. Please check the error log for further information.",0);
		}
	}*/
	
	Saint.stopEditFile = function() {
		Saint.sfmCurrentlyEditing = 0;
		$(".sfm-editblock").addClass("hidden");
		Saint.clearForm('#saint-file-info form');
		$('#saint-file-mode').val("search");
	};
	
	Saint.loadWysiwyg = function(target) {
		Saint.openAddBox();
		Saint.wysiwygTarget = '#'+target;
		var wysiwygForm = $('#saint_ajax_templates > .wysiwyg-form').clone().removeClass('template').removeClass('hidden');
		wysiwygForm.find('input[name=saint-wysiwyg-name]').val(target);
		wysiwygForm.find('textarea[name=saint-wysiwyg-content]').val($('#'+target).html());
		$('#saint-add-block-load').html(wysiwygForm);
		wysiwygForm.find('textarea[name=saint-wysiwyg-content]').focus();
		Saint.startMCE("#saint-admin-add-block .wysiwyg-editable");
		$('#saint-admin-add-block').removeClass("loading");
	};
	
	Saint.saveWysiwyg = function() {
		$('#saint-admin-add-block').addClass("loading");
		postdata = $('#saint-admin-add-block form').serialize();
		Saint.callHome("/system/",postdata,Saint.savedWysiwyg);
		Saint.closeAddBox();
	};
	
	Saint.savedWysiwyg = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				$(Saint.wysiwygTarget).html($('#saint-add-block-load').find('textarea[name=saint-wysiwyg-content]').val());
				$('#saint-add-block-load').html('');
			} else {
				Saint.addError("There was a problem saving your changes. Please check the error log for further information.",0);
				$('#saint_ajax_indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem saving your changes. Please check the error log for further information.",0);
		}
	};
	
	Saint.startMCE = function(mcetarget) {
		$(mcetarget).tinymce({
			// Location of TinyMCE script
			script_url : '/core/scripts/tinymce/tiny_mce.js',

			// General options
			theme : "advanced",
			plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			height: "500",
			length: "900",
			// Example content CSS (should be your site CSS)
			content_css : "/core/styles/saint.css",

			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",

			/*
			// Replace values for the template plugin
			template_replace_values : {
				username : "Some User",
				staffid : "991234"
			}*/
		});
	};
	
	Saint.savedCategory = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				$("#saint-delete-category").val('0');
				$("#saint-set-category-id").val('0');
				$("#saint-add-category").val('');
			} else {
				Saint.addError("There was a problem saving the selected category.");
				$('#saint_ajax_indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			alert(e);
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem saving the selected category. Please check the server error log for further information.",0);
		}
	};
	
	Saint.editUser = function(uid) {
		$('#saint_admin_dynamic_options').html('');
		$('#saint_admin_dynamic_options').addClass("loading");
		Saint.showOptions("#saint_admin_dynamic_options");
		var url = '/user/view.edit/id.'+uid;
		Saint.callHome(url,null, Saint.loadDynamicOptionsBox);
	};
	
	Saint.savedUser = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				Saint.showOptions("#saint_admin_user_options");
			} else {
				var err = "There was a problem editing selected user.";
				Saint.addError("There was a problem editing selected user.");
				$('#saint_ajax_indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem editing selected user. Please check the server error log for further information.",0);
		}
	};
	
	Saint.loadDynamicOptionsBox = function(data) {
		$('#saint_admin_dynamic_options').html(data);
		$('#saint_admin_dynamic_options').removeClass("loading");
	};
	
	Saint.openFileManager = function(label) {
		if (label != null) {
			var imgid = $(label).find('.sfl-image').attr('id').replace(/^sfid-/,'');
			var lblid = label.id.replace(/^sfl-/,'');
			Saint.sfmFileToEdit = imgid;
			Saint.sfmLabelToEdit = lblid; }
		Saint.fileManagerIsOpen = true;
		if (imgid != null) {
			Saint.sfmImageToLoad = imgid;
		}
		var callurl = "/filemanager";
		if (imgid != null) {
			callurl += "/fid."+imgid;
		}
		Saint.callHome(callurl,'',Saint.loadedFileManager);
		Saint.sfmAnimationIsComplete = false;
		$('#saint-admin-file-manager').addClass("loading").addClass("active");
		
		setTimeout(function(){
			Saint.sfmAnimationIsComplete = true;
			Saint.openImage();
		},600);
	};
	
	Saint.closeFileManager = function() {
		Saint.fileManagerIsOpen = false;
		$('#saint-admin-file-manager').removeClass("active");
	};
	
	Saint.loadedFileManager = function(data) {
		$('#saint-file-manager-load').html(data);
		if (Saint.sfmAnimationIsComplete) {
			Saint.openImage(); }
	};
	
	Saint.openImage = function() {
		if (Saint.sfmImageToLoad) {
			Saint.startEditFile($('#sfm-'+Saint.sfmImageToLoad).parent());
			Saint.sfmImageToLoad = 0;
			$('#saint-admin-file-manager').removeClass("loading");
		} else if (!Saint.sfmImageToLoad) {
			$('#saint-admin-file-manager').removeClass("loading"); }
	};

	Saint.openShopManager = function() {
		$('#saint-admin-shop-manager').addClass("loading").addClass("active");
		Saint.callHome('/shop/view.transactions','',Saint.loadedShopManager);
		Saint.shopManagerIsOpen = true;
	};
	
	Saint.closeShopManager = function() {
		$('#saint-admin-shop-manager').removeClass("active");
		Saint.shopManagerIsOpen = false;
	};
	
	Saint.loadedShopManager = function(data) {
		$('#saint-shop-manager-load').html(data);
		$('#saint-admin-shop-manager').removeClass("loading");
	};
	
	Saint.sfmSelectPage = function(pagenum,postdata) {
		Saint.sfmcurpage = pagenum;
		$('#saint-file-manager-data .saint-admin-block-overlay').addClass("loading");
		Saint.callHome("/system/view.file-list/sfmcurpage."+pagenum, postdata, Saint.sfmLoadPage);
	};
	
	Saint.sfmLoadPage = function(data) {
		$('#saint-file-manager-data .saint-loadable-content').html(data);
		if ($('#sfm-message').html() != "") {
			$('#sfm-message').addClass('enabled');
			setTimeout("$('#sfm-message').removeClass('enabled')",5000);
		}
		if ($('#sfm-status').html() == "saved") {
			Saint.clearForm('#saint-file-info form');
			$('#saint-file-mode').val("search");
			$('#saint-file-info .form-submit').html('Search');
			$('#saint-file-info .form-cancel').html('Reset');
		}
		$('#saint-file-manager-data .saint-admin-block-overlay').removeClass("loading");
	};
	
	Saint.startPageEdit = function() {
		$(document.body).addClass("editing");
		Saint.editing = true;
		$('#saint_admin_po_edit').html("Stop Editing");
		$('#saint-admin-page-options').addClass("visible");
		$('.editable').addClass("editnow");
		$('.repeating').addClass("editnow");
		Saint.contractOverlay();
	};
	
	Saint.stopPageEdit = function() {
		$(document.body).removeClass("editing");
		Saint.editing = false;
		if ($('#saint-admin-add-block').hasClass("active")) {
			Saint.saveAddBox();
			Saint.closeAddBox();
		}
		$('#saint_admin_po_edit').html("Edit This Page");
		$('#saint-admin-page-options').removeClass("visible");
		$('.editable').removeClass("editnow");
		$('.repeating').removeClass("editnow");
	};

	Saint.deleteCurrentPage = function() {
		var cpid = $('#saint-admin-page-options').find('input[name=saint_edit_page_id]').val();
		Saint.callHome("/system/delpage/"+cpid,'',Saint.deletedCurrentPage);
	};
	
	Saint.deletedCurrentPage = function(data) {
		try {
			realdata = JSON.parse(data);
			if (realdata['success']) {
				window.location.replace('/');
			} else {
				$('#saint_ajax_indicator').addClass("error");
			}
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem deleting your page. Please check the server error log for further information.",0);
		}
	};
	
	Saint.refreshPage = function() {
		location.reload(true);
	};
	
	Saint.startEdit = function(label) { 
		label.addClass('editing');
		var labelForm = $('#saint_ajax_templates > .label-form').clone().removeClass('template').removeClass('hidden');
		labelForm.find('.cache').html(label.html());
		labelForm.find('input[name=label-name]').val(label.attr('id'));
		labelForm.find('textarea[name=label-value]').val(label.html().replace(/<br\s*\/?>/mg,""));
		label.html(labelForm);
		labelForm.find('textarea[name=label-value]').focus();
		lines = labelForm.find('textarea[name=label-value]').val().split("\n");
		if (lines.length > 1) {
			multiplier = lines.length * 2;
		} else {
			multiplier = 1;
		}
		labelForm.find('textarea[name=label-value]').attr('rows',multiplier);
	};
	
	Saint.stopEdit = function(label) {
		if (label.hasClass('editing')) {
			label.removeClass('editing');
			label.html(label.find('.cache').html());
		}
	};
	
	Saint.expandOverlay = function() {
		if ($('#saint_admin_overlay').hasClass("contracted")) {
		$('#saint_admin_overlay').animate({
		    width: '500px',
		    height: '300px'
		  }, 600, function() {
			$('#saint_admin_overlay').removeClass("contracted");
			$('#saint_admin_overlay').addClass("expanded");
		  });
		}
	};
	
	Saint.contractOverlay = function() {
		if ($('#saint_admin_overlay').hasClass("expanded")) {
		  $('#saint_admin_overlay').animate({
		    width: '100px',
		    height: '20px'
		  }, 600, function() {
			$('#saint_admin_overlay').removeClass("expanded");
			$('#saint_admin_overlay').addClass("contracted");
		  });
		}
	};
	
	Saint.showOptions = function(thediv) {
		$('.saint_admin_options').addClass("hidden");
		$(thediv).removeClass("hidden");
	};
	
	Saint.openAddBox = function() {
		$('#saint-admin-add-block').addClass("loading").addClass("active");
	};
	
	Saint.closeAddBox = function() {
		$('#saint-admin-add-block').removeClass("active");
	};
	
	Saint.loadAddBox = function(data) {
		$('#saint-add-block-load').html(data);
		$('.editable').addClass("editnow");
		$('#saint-add-block-data .editable').addClass('editnow');
		$('#saint-admin-add-block').removeClass("loading");
	};
	
	Saint.saveAddBox = function() {
		$('#saint-admin-add-block').addClass("loading");
		postdata = $('#saint-add-block-settings').serialize();
		Saint.callHome("/system/edit."+$('#saint-block-setting-id').val(),postdata,Saint.savedAddBox);
	};
	
	Saint.savedAddBox = function(data) {
		$('#saint-admin-add-block').removeClass("loading");
		try {
			realdata = JSON.parse(data);
			if (!realdata['success'])
				$('#saint_ajax_indicator').addClass("error");
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem adding your page. Please check the server error log for further information.",0);
		}	
	};
	


	Saint.stripTags = function (input, allowed) {
	    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
	    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	    });
	}
	
	Saint.saveLabel = function(label) {
		var stripped = Saint.stripTags(label.find('textarea[name=label-value]').val(),'<a><i><b><p><ul><li><img><h1><h2><h3><h4><h5><h6>');
		//stripped = (stripped + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');
		label.find('.cache').html(stripped);
		var sdata = label.find('form').serialize();
		Saint.callHome("/",sdata,Saint.savedLabel);
	};
	
	Saint.savedLabel = function(data) {
		try {
			realdata = JSON.parse(data);
			if (!realdata['success'])
				$('#saint_ajax_indicator').addClass("error");
			Saint.setActionLog(realdata.actionlog);
		} catch (e) {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("Error saving label. Please check the server error log for further information.");
		}
	};
	
	Saint.addPage = function() {
		postdata = $('#saint_admin_page_add form').serialize();
		Saint.callHome('/',postdata,Saint.addedPage);
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
			Saint.showOptions("#saint_admin_page_options");
			$(':input','#saint_admin_page_add')
			 .not(':button, :submit, :reset, :hidden')
			 .val('')
			 .removeAttr('checked')
			 .removeAttr('selected');
		} else {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem adding your page. Please check the error log for further information.",0);
		}
	};
	
	Saint.savePageOptions = function() {
		postdata = $('#saint-admin-page-options form').serialize();
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
			Saint.showOptions("#saint_admin_page_options");
			$(':input','#saint_admin_page_add')
			 .not(':button, :submit, :reset, :hidden')
			 .val('')
			 .removeAttr('checked')
			 .removeAttr('selected');
		} else {
			$('#saint_ajax_indicator').addClass("error");
			Saint.addError("There was a problem saving your page options. Please check the error log for further information.",0);
		}
	};
	
	Saint.callHome = function(url, postdata, complete, timeout, retries, tcid, errorno) {
		if (retries <= 0) {
			$('#saint_ajax_indicator').addClass("error");
			if (errorno == null)
				errorno = Saint.addError("There has been a problem communicating with the server. Please check your network connections before continuing. Any changes you make at this time will not be saved.",0);
		}
		if (timeout == null)
			timeout = 10000;
		if (retries == null)
			retries = 3;
		if (tcid == null)
			tcid = Saint.pushConnection(url, postdata);
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
		if (!$('#saint_ajax_indicator').hasClass("enabled"))
			$('#saint_ajax_indicator').addClass("enabled");
		ncid = Saint.connections.push(new Array(url,data));
		return ncid-1;
	};
	
	Saint.popConnection = function(tcid) {
		Saint.connections.splice(tcid,1);
		if(!Saint.connections.length)
			$('#saint_ajax_indicator').removeClass("enabled");
	};
	
	Saint.addError = function(error, severity) {
		var esize = Saint.errors.push(error);
		if (!$('#saint_ajax_indicator').hasClass("error"))
			$('#saint_ajax_indicator').addClass("error");
		if (severity == 0)
			alert(error);
		return esize-1;
	};
	
	Saint.addEvent = function(event) {
		$('#saint_admin_event_log').append("<p>"+ event +"</p>");
	};
	
	Saint.clearActionLog = function() {
		$('#saint_admin_event_log').html('');
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
			$('#saint_ajax_indicator').removeClass("error");
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
});
