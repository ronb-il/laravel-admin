var Netotiate = Netotiate || {};   
Netotiate.Translator = Netotiate.Translator || {};
Netotiate.Translator.Dictionary = Netotiate.Translator.Dictionary || {};

_t = function(term, properties){
	var translated = "";
	
	if(term == "undefined" ||  term == ""){
		return "";
	}
	
	try{
		var matches =  term.match(/^(.+)\.(.+)$/);
		if(!matches)
			return term;
		
		var namespace = matches[1];
		translated = term = matches[2].toLowerCase();


		if( Netotiate.Translator.Dictionary[namespace] != "undefined" && Netotiate.Translator.Dictionary[namespace][term] != "undefined" && Netotiate.Translator.Dictionary[namespace][term] != null ){
			translated = Netotiate.Translator.Dictionary[namespace][term];
		}
		
		//Properties mixing
		if( typeof properties == "object" && $(properties).length >= 1 ){//At least key and value
			for(var prop in properties) {
			    if(properties.hasOwnProperty(prop)){
			    	var key = prop;
				    var value = properties[prop];
				    
				    translated = translated.replace(key, value);
			    }
			}
		}

	}catch(e){}
 
	return translated;
};