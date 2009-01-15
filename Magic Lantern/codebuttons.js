defmode = "normalmode"; // default mode. either normalmode, advmode, or helpmode

if (defmode == "advmode") {
	helpmode = false;
	normalmode = false;
	advmode = true;
} else if (defmode == "helpmode") {
	helpmode = true;
	normalmode = false;
	advmode = false;
} else {
	helpmode = false;
	normalmode = true;
	advmode = false;
}
function chmode(swtch){
	if (swtch == 1){
		advmode = false;
		normalmode = false;
		helpmode = true;
		alert("Help Mode\nClick on any of the formatting buttons for a description and instructions.");
	} else if (swtch == 0) {
		helpmode = false;
		normalmode = false;
		advmode = true;
		alert("Advanced Mode\nThe BB Code will be inserted without options as soon as you hit the button.");
	} else if (swtch == 2) {
		helpmode = false;
		advmode = false;
		normalmode = true;
		alert("Normal Mode\nPopups will bring you step by step through the process of inserting BB Code.");
	}
}

function AddText(NewCode) {
	document.input.message.value+=NewCode
	setfocus()
}

function email() {
	if (helpmode) {
		alert("Email Tag\nTurns an email address into a mailto hyperlink.\nUsage: [email]someone@anywhere.com[/email]\nUsage: [email=someone@anywhere.com]link text[/email]");
	} else if (advmode) {
		AddTxt="[email] [/email]";
		AddText(AddTxt);
	} else { 
		txt2=prompt("What name should be shown\nIf this field is empty the email address will be visible",""); 
		if (txt2!=null) {
			txt=prompt("Please enter an email address.","name@domain.com");      
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[email]"+txt+"[/email]";
                
				} else {
					AddTxt="[email="+txt+"]"+txt2+"[/email]";
				} 
				AddText(AddTxt);	        
			}
		}
	}
}


function chsize(size) {
	if (helpmode) {
		alert("Size Tag\\nSets the text size.\\nPossible values are 1 to 6.\\n 1 being the smallest and 6 the largest.\\nUsage: [size="+size+"]This is size "+size+" text[/size]");
	} else if (advmode) {
		AddTxt="[size="+size+"] [/size]";
		AddText(AddTxt);
	} else {                       
		txt=prompt("Please enter the text to be size "+size,"Text"); 
		if (txt!=null) {             
			AddTxt="[size="+size+"]"+txt+"[/size]";
			AddText(AddTxt);
		}        
	}
}

function chfont(font) {
 	if (helpmode){
		alert("Font Tag\\nSets the font face for the enclosed text.\\nUsage: [font="+font+"]The font of this text is "+font+"[/font]");
	} else if (advmode) {
		AddTxt="[font="+font+"] [/font]";
		AddText(AddTxt);
	} else {                  
		txt=prompt("Please enter the text to be in "+font,"Text");
		if (txt!=null) {             
			AddTxt="[font="+font+"]"+txt+"[/font]";
			AddText(AddTxt);
		}        
	}  
}


function bold() {
	if (helpmode) {
		alert("Bold Tag\nMakes the enlosed text bold.\nUsage: [b]This is some bold text[/b]");
	} else if (advmode) {
		AddTxt="[b] [/b]";
		AddText(AddTxt);
	} else {  
		txt=prompt("Please enter the text that should be bolded.","Text");     
		if (txt!=null) {           
			AddTxt="[b]"+txt+"[/b]";
			AddText(AddTxt);
		}       
	}
}

function italicize() {
	if (helpmode) {
		alert("Italicize Tag\nMakes the enlosed text italicized.\nUsage: [i]This is some italicized text[/i]");
	} else if (advmode) {
		AddTxt="[i] [/i]";
		AddText(AddTxt);
	} else {   
		txt=prompt("Please enter the text that should be italicized.","Text");     
		if (txt!=null) {           
			AddTxt="[i]"+txt+"[/i]";
			AddText(AddTxt);
		}	        
	}
}

function quote() {
	if (helpmode){
		alert("Quote tag\nQuotes the enclosed text to reference something specific that someone has posted.\nUsage: [quote]This is a quote[/quote]");
	} else if (advmode) {
		AddTxt="\r[quote]\r[/quote]";
		AddText(AddTxt);
	} else {   
		txt=prompt("Please enter the text you want quoted.","Text");     
		if(txt!=null) {          
			AddTxt="\r[quote]\r"+txt+"\r[/quote]";
			AddText(AddTxt);
		}	        
	}
}

function chcolor(color) {
	if (helpmode) {
		alert("Color Tag\nSets the text color.  Any named color can be used.\nUsage: [color="+color+"]This is some "+color+" text[/color]");
	} else if (advmode) {
		AddTxt="[color="+color+"] [/color]";
		AddText(AddTxt);
	} else {  
     	txt=prompt("Please enter the text that should be "+color,"Text");
		if(txt!=null) {
			AddTxt="[color="+color+"]"+txt+"[/color]";
			AddText(AddTxt);        
		} 
	}
}

function center() {
 	if (helpmode) {
		alert("Centered tag\nCenters the enclosed text.\nUsage: [align=center]This text is centered[/align]");
	} else if (advmode) {
		AddTxt="[align=center] [/align]";
		AddText(AddTxt);
	} else {  
		txt=prompt("Please enter the text that should be centered.","Text");     
		if (txt!=null) {          
			AddTxt="\r[align=center]"+txt+"[/align]";
			AddText(AddTxt);
		}	       
	}
}

function hyperlink() {
	if (helpmode) {
		alert("Hyperlink Tag\nTurns an url into a hyperlink.\nUsage: [url]http://www.anywhere.com[/url]\nUsage: [url=http://www.anywhere.com]link text[/url]");
	} else if (advmode) {
		AddTxt="[url] [/url]";
		AddText(AddTxt);
	} else { 
		txt2=prompt("What name should be shown?\nIf this Field is blank the URL would be visible",""); 
		if (txt2!=null) {
			txt=prompt("Please enter the url for the hyperlink.","http://");      
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[url]"+txt+"[/url]";
					AddText(AddTxt);
				} else {
					AddTxt="[url="+txt+"]"+txt2+"[/url]";
					AddText(AddTxt);
				}         
			} 
		}
	}
}

function image() {
	if (helpmode){
		alert("Image Tag\nInserts an image into the post.\nUsage: [img]http:\www.anywhere.comimage.gif[/img]");
	} else if (advmode) {
		AddTxt="[img] [/img]";
		AddText(AddTxt);
	} else {  
		txt=prompt("Please enter the URL to the image you wish to insert.","http://");    
		if(txt!=null) {            
			AddTxt="\r[img]"+txt+"[/img]";
			AddText(AddTxt);
		}	
	}
}

function code() {
	if (helpmode) {
		alert("Code Tag\nBlockquotes the text you reference and preserves the formatting.\nUsefull for posting code.\nUsage: [code]This is formated text[/code]");
	} else if (advmode) {
		AddTxt="\r[code]\r[/code]";
		AddText(AddTxt);
	} else {   
		txt=prompt("Please enter the text you wanted blockquoted.","");     
		if (txt!=null) {          
			AddTxt="\r[code]"+txt+"[/code]";
			AddText(AddTxt);
		}	       
	}
}

function list() {
	if (helpmode) {
		alert("List Tag\nBuilds a bulleted, numbered, or alphabetical list.\nUsage: [list]\n[*]item1\n[*]item2\n[*]item3\n[/list]");
	} else if (advmode) {
		AddTxt="\r[list]\r[*]\r[*]\r[*]\r[/list]";
		AddText(AddTxt);
	} else {  
		txt=prompt("Please select your list type\n ('A' for aplhabetic, '1' for nummeric) You can leave this field blank","");               
		while ((txt!="") && (txt!="A") && (txt!="a") && (txt!="1") && (txt!=null)) {
			txt=prompt("ERROR!\nThe only possible values for type of list are blank 'A' and '1'.","");               
		}
		if (txt!=null) {
			if (txt=="") {
				AddTxt="\r[list]\r\n";
			} else {
				AddTxt="\r[list="+txt+"]\r";
			} 
			txt="1";
			while ((txt!="") && (txt!=null)) {
				txt=prompt("List Item:\nLeave it blank to End the List",""); 
				if (txt!="") {             
					AddTxt+="[*]"+txt+"\r"; 
				}                   
			} 
			AddTxt+="[/list]\r\n";
			AddText(AddTxt); 
		}
	}
}

function underline() {
  	if (helpmode) {
		alert("Underline Tag\nUnderlines the enclosed text.\nUsage: [u]This text is underlined[/u]");
	} else if (advmode) {
		AddTxt="[u] [/u]";
		AddText(AddTxt);
	} else {  
		txt=prompt("Please enter the text that should be underlined.","Text");     
		if (txt!=null) {           
			AddTxt="[u]"+txt+"[/u]";
			AddText(AddTxt);
		}	        
	}
}

function setfocus() {
	document.input.message.focus();
}
