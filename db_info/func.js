////////////////////////////////////////////////////////////////////////
// show a help div.
// eg:
// <span onmouseover="show_help('mytestword');" onmouseout="show_help('');"> just a test for my script </span>
document.write("<div id=\"div_help\" style=\"position: absolute;z-index: 100; visibility: hidden; filter: alpha(opacity=85);background: #ddeeff; border: 1pt solid steelblue;\">aaa</div>");
function show_help(help_string,event)
{
    var div_help=document.getElementById('div_help');
    var my_visibility=div_help.style.visibility;
    if (my_visibility=="hidden")
    {
        div_help.style.left = ( event.clientX + document.body.scrollLeft + 10) + 'px';
	div_help.style.top = ( event.clientY + document.body.scrollTop + 10) + 'px';
	div_help.innerHTML=help_string;
        div_help.style.visibility='visible';
    }
    else
    {
        div_help.style.visibility='hidden';
    }
    //alert(document.all.div_help.innerHTML);
}
