/* jSelect (using jQuery library).
*--------------------------------------------*
*  @author : ukhome ( ukhome@gmail.com | ntkhoa_friends@yahoo.com )
*--------------------------------------------*
*  @released : 24-Mar-2009 : version 1.0
*--------------------------------------------*
*  @revision history : ( latest version : 1.0 )
*--------------------------------------------*
*      + 24-Mar-2009 : version 1.0
*          - released
*--------------------------------------------*
*/

/* External Interface
*/

function callExternalFunction (o/*caller*/, droplists/*all droplists*/, val/*rel in <a>*/) {
    /*
    * o : selectUI object
    *   o.select : <select> in jQuery type
    *   o.elUL : list drop down, main list <ul>
    *----------------------------------------------*
    * droplists : all selectUI droplists in page, call by its id droplists(id), will return selectUI object
    * val : rel value in a of each selectUI option
    */
    o.select.trigger("onchange");
    //o.select.attr("id") <-- check current select id
	


}