function changeValid(url, user) {
	var data = 'user='+user+'&valid='+($('valid_'+user).checked ? 'yes':'no');
	if($chk($('field1_'+user)) && $('field1_'+user).checked) data += '&field1=yes';
	if($chk($('field2_'+user)) && $('field2_'+user).checked) data += '&field2=yes';
	if($chk($('field3_'+user)) && $('field3_'+user).checked) data += '&field3=yes';
	if($chk($('public_'+user)) && $('public_'+user).checked) data += '&public=yes';
	ajaxRequest('post', url, data, 'changeValidResult'+user);
}
