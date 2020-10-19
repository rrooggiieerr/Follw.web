# Follw

Follw is a privacy focused location sharing service. Only a unique Sharing ID and derived Sharing URL is given and no account details, user credentials, IP addresses, Cookies and other sensitive information are used or stored on the Follw servers.

Whenever a new location is submitted the previous location is overwritten, no location history is stored.

Whenever you delete your unique Sharing ID all location details are removed from the Follw servers. Only a hash of your Sharing ID is stored after removal to guarantee a Sharing ID is not reassigned again.

# Installation

* Copy Follw.web one level below your webroot directory
* Create database
* Create Follw.web/config.php with your database configuration
* Move the contents of Follw.web/htdocs to your webroot directory