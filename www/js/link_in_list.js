  var ul = document.getElementById("ul1");
  var links = document.getElementsByTagName("a");
  for(i=0; i<links.length; i++){
    var li = document.createElement("li");
	var a = links[i];
	ul.replaceChild(li, a);
	li.appendChild(a);
  }
