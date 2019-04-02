// This line is needed to get a fake XMLHttpRequest object; this thing is
// normally provided directly by the browser, so we need to get one here from
// the nodejs library.
var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;
var xmlhttp = new XMLHttpRequest();

// A callback to handle a completed server call.
var xmlhttp_callback;
xmlhttp.onreadystatechange = function() {
	if (this.readyState === 4) {
		requestDone = true;
		xmlhttp_callback();
	}
}


// The "piggie" object organizes the main functions a player needs to do: init
// (login), startGame, play, purchase.	
piggie = {
	// This is the first the then player does.
	init: function() {
		console.log("Starting init...");
		var startTime = Date.now();
		xmlhttp_callback = function() {
			console.log("login.php call complete.");

			// Process the results.	We just parse the response and save the
			// data pieces in global variables.
			try {
				var resp = JSON.parse(xmlhttp.responseText);
				if (resp.status == "error") {
					console.log("INIT server error: " + resp.message);
				} else {
					user = JSON.parse(JSON.stringify(resp.user));
					collections = JSON.parse(JSON.stringify(resp.collections));
					config = JSON.parse(JSON.stringify(resp.config));
					console.log("User: " + user);
				}
			} catch (err) {
				console.log("LOGIN response error: " + err + "\n\n" + xmlhttp.responseText);
			}
		};
		
		// Do the login call.
		xmlhttp.open("POST", gsURL + "login.php?id=123456&snip=localhost", true);
		try {
			xmlhttp.send();
		} catch (err) {
			console.log("INIT FAIL: " + err);
		}
	}
}


gsURL = "http://localhost/server/";
piggie.init();	

