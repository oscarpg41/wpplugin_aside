jQuery(document).ready(function() {

	jQuery('#upload_image_button').click(function() {
		formfield = jQuery('#upload_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		jQuery('#upload_image').val(imgurl);
		tb_remove();
	}

	jQuery('.btnDeleteAside').click(function() {
		var url = "admin.php?page=opg_aside&task=remove_aside&id=" + this.id;
	    var r = confirm("Está seguro de eliminar este registro?");
	    if (r == true) {
			window.location = url; 
	    }
	});
});