//import express framework
var http = require("https");
var async = require("async");
var express = require('express');
var path = require('path');
var bodyParser = require("body-parser");
var app = express();

//enable bodyparser middleware
//bodyparser.urlencoded returns middleware that only parses urlencoded bodies
//bodyparser.json returns middleware that only parses json
app.use(bodyParser.urlencoded({
  extended: false
}));
app.use(bodyParser.json());

//enable CORS
app.use(function(req, res, next) {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
  next();
});

// expose nominee ID
// app.use() for binding middle-ware to application, will respond to any path regardless of HTTP method used (i.e. GET, PUT, POST etc)
// app.get() is application routing specifically for GET requests 
app.get('/nomineeID', function(req, res, next) {
  var nomData = req.param("nominee");
  console.log("Call to nominee id", nomData);
  APIcall("user?q=" + nomData, "GET", null, function(response) {
    var requestResponse = '';
    response.on('data', function(chunk) {
      requestResponse += chunk;
      data = JSON.parse(requestResponse);
      nomineeID = data.content['items'][0]['id'];
      console.log(nomineeID);
      return (nomineeID);
    })
    response.on('end', function() {
      res.json(nomineeID); //res.json sends json response, can also use res.send but only res.json will convert non-objects like null, undefined
    })
  });
});

//expose nominator ID
app.get('/nominatorID', function(req, res, next) {
  var nominatorData = req.param("nominator");
  console.log("nominator id", nominatorData);
  APIcall("user?q=" + nominatorData, "GET", null, function(response) {
    var requestResponse = '';
    response.on('data', function(chunk) {
      requestResponse += chunk;
      data = JSON.parse(requestResponse);
      nominatorID = data.content['items'][0]['id'];
      return (nominatorID);
    })
    response.on('end', function() {
      res.json(nominatorID);
    })
  });
});

//expose post endpoint
app.post('/recognitionPOST', function(req, res, next) {
  var postData = req.body;
  APIcall("recognition", "POST", postData, function() {
    console.log("recognition posted!")
  })
  res.end("Your recognition has been posted!");
});

//listen to localhost port 3000, has to read off env variable
var port = Number(process.env.PORT || 3000);
app.listen(port);
console.log('Listening on port 3000...');

//function to call Achievers API
function APIcall(endpoint, method, data, callback) {
  
  var programURL = "masterdemo.sandbox.achievers.com"
  
  switch (method) {
    case 'GET':
      var options = {
        "method": method,
        "hostname": programURL,
        "path": "/api/v3/" + endpoint,
        "headers": {
          "accept": "application/json",
          "content-type": "application/json",
          "authorization": 'Basic XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' //real credentials hidden 
        }
      };
      var request = http.request(options, callback)
      request.end();

      break;

    case 'POST':
      var options = {
        "method": method,
        "hostname": programURL,
        "path": "/api/v3/" + endpoint,
        "headers": {
          "accept": "multipart/form-data",
          "content-type": "application/json",
          "authorization": "Basic XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", //real credentials hidden 
          "cache-control": "no-cache",
        }
      };
      var request = http.request(options, callback)
      request.write(JSON.stringify(data)) 
      request.end(); //end request
      break;

    default:
      console.log("error");
  }
}
