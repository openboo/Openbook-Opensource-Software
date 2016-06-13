/**
SAL - Simple Ajax Lib. 23-Sep-2005
by Nigel Liefrink
Email: leafrink@hotmail.com
*/

var debug = false;
/**
<summary>
Browser Compatability function.
Returns the correct XMLHttpRequest 
  depending on the current browser.
</summary>
*/
function GetXmlHttp() {
  var xmlhttp = false;
  if (window.XMLHttpRequest)
  {
    xmlhttp = new XMLHttpRequest()
  }
  else if (window.ActiveXObject)// code for IE
  {
    try
    {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP")
    } catch (e) {
      try
      {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
      } catch (E) {
        xmlhttp=false
      }
    }
  }
  return xmlhttp;
}


/**
<summary>
Gets the response stream from the passed url, and then calls
   the callbackFuntion passing the response and the div_ids.
</summary>
<param name="url">The url to make the request
            to get the response data.</param>
<param name="callbackFunction">The function to call after 
the response has been recieved.
The response <b>must</b> always 
be the first argument to the function.</param>
<param name="params"> (optional) Any other parameters 
you want to pass to the functions.
(Note: only constants/strings/globals can be passed
       as params, most variables will be out of scope.)
</param>
<example>
    <code>
PassAjaxResponseToFunction('?getsomehtml=1', 
              'FunctionToHandleTheResponse', 
              "\'div1\',\'div2\',\'div3\'');

function FunctionToHandleTheResponse(response, d1, d2, d3){
    var data = response.split(';');
    document.getElementById(d1).innerHTML = data[0];
    document.getElementById(d2).innerHTML = data[1];
    document.getElementById(d3).innerHTML = data[2];
}
    </code>
</example>
*/
function PassAjaxResponseToFunction(url, callbackFunction, params)
{
  var xmlhttp = new GetXmlHttp();
  //now we got the XmlHttpRequest object, send the request.
  if (xmlhttp)
  {
    xmlhttp.onreadystatechange = 
            function ()
            {
              if (xmlhttp && xmlhttp.readyState==4)
              {//we got something back..
                if (xmlhttp.status==200)
                {
                  var response = xmlhttp.responseText;
                  var functionToCall = callbackFunction + 
                                 '(response,'+params+')';
                  if(debug)
                  {
                    alert(response);
                    alert(functionToCall);
                  }
                  eval(functionToCall);
                } else if(debug){
                  document.write(xmlhttp.responseText);
                }
              }
            }
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
  }
}


/**
<summary>
Sets the innerHTML property of obj_id with the response from the passed url.
</summary>
<param name="url">The url to make the request 
to get the response data.</param>
<param name="obj_id">The object or the id of 
the object to set the innerHTML for.</param>
*/
function SetInnerHTMLFromAjaxResponse(url, obj_id)
{
  var xmlhttp = new GetXmlHttp();
  //now we got the XmlHttpRequest object, send the request.
  if (xmlhttp)
  {
    xmlhttp.onreadystatechange = 
            function ()
            {
              if (xmlhttp && xmlhttp.readyState==4)
              {//we got something back..
                if (xmlhttp.status==200)
                {
                                  if(debug)
                                  {
                    alert(xmlhttp.responseText);
                  }
                  if(typeof obj_id == 'object')
                  {
                    obj_id.innerHTML = xmlhttp.responseText;
                  } else {
                    document.getElementById(obj_id).innerHTML = 
                                          xmlhttp.responseText;
                  }
                } else if(debug){
                  document.Write(xmlhttp.responseText);
                }
              }
            }
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
  }
}

/**
<summary>
Sets the value property of obj_id with the response from the passed url.
</summary>
<param name="url">The url to make the request 
to get the response data.</param>
<param name="obj_id">The object or the id of 
the object to set the value for.</param>
*/
function SetValueFromAjaxResponse(url, obj_id)
{
  var xmlhttp = new GetXmlHttp();
  //now we got the XmlHttpRequest object, send the request.
  if (xmlhttp)
  {
    xmlhttp.onreadystatechange = 
            function ()
            {
              if (xmlhttp && xmlhttp.readyState==4)
              {//we got something back..
                if (xmlhttp.status==200)
                {
                                  if(debug)
                                  {
                    alert(xmlhttp.responseText);
                  }
                  if(typeof obj_id == 'object')
                  {
                    obj_id.value = xmlhttp.responseText;
                  } else {
                    document.getElementById(obj_id).value = 
                                          xmlhttp.responseText;
                  }
                } else if(debug){
                  document.Write(xmlhttp.responseText);
                }
              }
            }
    xmlhttp.open("GET",url,true);
    xmlhttp.send(null);
  }
}
