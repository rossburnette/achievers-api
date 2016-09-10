Sharepoint integration version 1.0 9/9/2016

FILE LIST
-------------------------
Sharepoint.html - This is the front end component that can be placed into a SharePoint Code Snippet widget 
Server.js - This is the back end component that will be on a server
Procfile - For declaring what commands should be run by the application's dynos on the Heroku platform
Package.json - Metadata relevant to application (i.e. packages required by the application)


GENERAL USAGE NOTES
-------------------------
Disclaimer: This code was written for demo purposes and serves only as an example/starting point for anyone who is looking to create a similar integration, 
limitations of this specific code include the following:
-Social recognition only
-Hard-coded to a specific recognition criteria
-Does not include all users in the program, the list of users to search for is hard-coded
In a real integration scenario, it is recommended to use our usersearch API, select recognition criteria and tie this to a point based recognition

-The back end component for this sample integration is hosted on a free Heroku server. You can use any middleware app server to setup with the server.js script. 
The middleware is required since the Achievers API cannot be accessed through browser based applications for security reasons. The SharePoint widget needs to 
connect to a middleware that will connect to the Achievers server.


OVERVIEW OF THE INTEGRATION WORKS
--------------------------
1.	Front-end code on SharePoint (sharepoint.html) grabs the SharePoint login ID of the user (nominator) that is currently logged into
SharePoint and matches this to the user’s Achiever ID and places this in a variable
2.	Front-end code on SharePoint grabs the name of the nominee that is entered in the modal window and matches this to the nominee’s Achiever
ID and places this in a variable
3.	Front-end code on SharePoint grabs the recognition text and places this in a variable
4.	The information from 1-3 is sent to a proxy server/ middleware (server.js) which then uses this information to make a POST call to our API. 

	
INTEGRATION USE CASE
-------------------------
Users are already familiar with their company’s intranet. It is often the default homepage for their browser at work and a centralized place they go to for company 
information. Integrating an intranet like SharePoint with Achievers allow users to easily recognize their colleagues without navigating to a different webpage. 


RECOGNITION FLOW FROM SHAREPOINT
-------------------------
1.	User hits recognize button on SharePoint page
2.	Modal window pops up
3.	User searches for colleague they wish to recognize
4.	User types out recognition
5.	User hits “Recognize button” in modal window
6.	Confirmation pops up to confirm the recognition has been posted





