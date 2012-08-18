/**
 * poMMo's CKEditor Configuration.
 * 
 * Default file is ckeditor/config.js
 *   
 * Changes to this file will override the default settings.
 */

CKEDITOR.editorConfig = function( config )
{
	config.fullPage = false ;
	config.docType = '' ;
	config.baseHref = '' ;
	    
	config.extraPlugins = 'tableresize';
	    
	config.height = 300; // Make the default height a little bigger
	config.resize_dir = 'vertical';

	config.toolbar = [
		['Cut','Copy','Paste','PasteText','PasteWord'],
		['Undo','Redo','-','Find','Replace','-','SelectAll'],
		['SpecialChar','Rule'],
		['Scayt','-','ShowBlocks'],
		'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript','RemoveFormat'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		['Image','Table'],
		'/',
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize']
	] ;
	    
	config.enterMode = CKEDITOR.ENTER_P ;			// p | div | br
	config.shiftEnterMode = CKEDITOR.ENTER_BR ;	// p | div | br
	config.font_names		= 'Arial;Comic Sans MS;Courier New;Eurostile;Gill Sans;Tahoma;Times New Roman;Verdana' ;
	config.undoStackSize = 16 ;
	    
	config.filebrowserBrowseUrl = false ;
	config.filebrowserImageBrowseUrl = false ;
	config.filebrowserFlashBrowseUrl = false ;
	config.filebrowserUploadUrl = false ;
	config.filebrowserImageUploadUrl = false ;
	config.filebrowserFlashUploadUrl = false ;
};




