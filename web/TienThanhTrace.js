/*
 * Creates a console for debugging
 * Tien Thanh Control
 */
var Diagnostics = {counter:0, colIndex:0, colors:["black", "#222222"]};

//
// Write Trace Output
//
var html = '<div style="display:block; width: 920px; background-color: silver; margin:10px 0px 0px 0px;">Trace:<br /><div style="overflow:scroll;background-color: black; border: dotted 0px blue; padding: 0px; height:250px;">';
html+='<table id="oTraceText" style="width: 100%; border: solid 0px blue; color: White"></table>';
html+='</div>JS Code:<br/><textarea id=oJSCode style="width: 400px; height:200px"></textarea><br/><input type="button" value="Exec" onclick="Diagnostics.ExecJSDebugCode()">';
html+='</div>';
document.write(html);


//
// Global method Trace
//
Diagnostics.Trace=function(text, color)
{
	Diagnostics.colIndex = (Diagnostics.colIndex + 1) % Diagnostics.colors.length;
    var time = new Date();
    var o = document.getElementById("oTraceText");
    var row = o.insertRow(-1);
    var cell = row.insertCell(-1);
    cell.innerHTML = "[" + (Diagnostics.counter++) + "] " + text;
    cell.style.color = color || "white";
    cell.style.backgroundColor = Diagnostics.colors[Diagnostics.colIndex];
}

Diagnostics.ExecJSDebugCode=function()
{
    try
    {
        var o = document.getElementById("oJSCode");
        eval(o.value);
    }
    catch (e)
    {
        Diagnostics.Trace(e.message);
    }
}