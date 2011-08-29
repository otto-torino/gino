function get_radio_value(){
	for(var i=0; i < document.jump.var1.length; i++)
	{
		if(document.jump.var1[i].checked)
		{
			var rad_val = document.jump.var1[i].value;
		}
	}
	return(rad_val);
}

function  jump_radio(){
	var link=document.jump.jslink;
	var choice1=get_radio_value();
	self.location.href=link.value+"&var1="+choice1+"#a1";
}

